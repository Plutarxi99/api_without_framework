<?php

require_once __DIR__ . '/../db.php';

class MailerRepository
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: getPdo();
    }

    /**
     * Создать рассылку
     *
     * @param  string  $title  Заголовок рассылка
     * @param  string  $body  Тело рассылки
     *
     * @return int
     * @throws PDOException
     * @throws Exception
     */
    public function create(string $title, string $body): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO mailers (title, body) VALUES (?, ?)");
        $stmt->execute([$title, $body]);
        if (! $stmt->execute([$title, $body])) {
            throw new Exception("Failed to create mailer");
        }
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Получить одну рассылку
     *
     * @param int $id ID рассылки
     *
     * @return array
     */
    public function get(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mailers WHERE id = (?)");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    /**
     * Получить список рассылок
     *
     * @param int $limit  лимит
     * @param int $offset страницы
     *
     * @return array<int,array>
     */
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, title, body, created_at, updated_at
             FROM mailers
             ORDER BY id DESC
             LIMIT ? OFFSET ?"
        );
        // bind as integers
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}