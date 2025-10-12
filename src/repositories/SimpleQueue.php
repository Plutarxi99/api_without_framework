<?php

use enums\StatusSendEnum;

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../enums/StatusSendEnum.php';

class SimpleQueue
{
    private PDO $pdo;
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? getPdo();
    }


    /**
     * Простая постановка всех recipients в очередь для данной рассылки.
     *
     * @param int $mailer_id id рассылки
     *
     * @return int
     */
    public function enqueueAll(int $mailer_id): int
    {
        // сколько было до
        $count_stmt = $this->pdo->prepare("SELECT COUNT(*) FROM mailer_queue WHERE mailer_id = ?");
        $count_stmt->execute([$mailer_id]);
        $before = (int) $count_stmt->fetchColumn();

        $sql = "INSERT IGNORE INTO mailer_queue (mailer_id, recipient_id)
            SELECT ?, id FROM recipients";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mailer_id]);

        // сколько стало после
        $count_stmt->execute([$mailer_id]);
        $after = (int) $count_stmt->fetchColumn();

        return max(0, $after - $before);
    }

    /**
     * пометить N queued как sent
     *
     * @param int $mailer_id Id расслки
     * @param int $limit     Лимит
     *
     * @return int
     */
    public function sendBatch(int $mailer_id, int $limit = 100): int
    {
        $limit = max(1, $limit);

        // получаем значения enum как int
        $send = StatusSendEnum::SEND->value;
        $no_send = StatusSendEnum::NO_SEND->value;

        $sql = "UPDATE mailer_queue
                SET status = $send, last_attempt_at = NOW()
                WHERE mailer_id = ? AND status = $no_send
                LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mailer_id]);

        return $stmt->rowCount();
    }

    /**
     * Получение статистики рассылки
     *
     * @param int $mailer_id ID рассылки
     *
     * @return int[]
     */
    public function status(int $mailer_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS total,
                   SUM(status = ?) AS queued,
                   SUM(status = ?) AS sent,
                   SUM(status = ?) AS failed
            FROM mailer_queue WHERE mailer_id = ?
        ");
        $stmt->execute(
            [
                StatusSendEnum::NO_SEND->value,
                StatusSendEnum::SEND->value,
                StatusSendEnum::FAILED->value,
                $mailer_id
            ]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0,'queued' => 0,'sent' => 0,'failed' => 0];
    }
}