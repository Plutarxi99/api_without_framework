<?php

require_once __DIR__ . '/../db.php';

/**
 * Запись участников из файла
 */
class RecipientRepository {
    private PDO $pdo;
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? getPdo();
    }

    /**
     * Вставка в бд записей полученных в аргументе
     *
     * @param array $rows Массив, который надо вставить
     *
     * @return int
     */
    public function insertBatch(array $rows): int {
        if (empty($rows)) {
            return 0;
        }

        $place = [];
        $vals = [];

        foreach ($rows as $r) {
            if (
                    array_key_exists('phone', $r) ||
                    array_key_exists('email', $r)
            ) {
                $place[] = "(?, ?)";
                $vals[] = $r['phone'];
                $vals[] = $r['name'];
            }
        }

        $sql = "INSERT IGNORE INTO recipients (phone, name) VALUES " . implode(',', $place);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($vals);

        return $stmt->rowCount();
    }
}