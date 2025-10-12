<?php
// src/repositories/RecipientRepository.php
require_once __DIR__ . '/../db.php';

class RecipientRepository {
    private PDO $pdo;
    public function __construct(PDO $pdo = null) {
        $this->pdo = $pdo ?? getPdo();
    }

    public function insertBatch(array $rows): int {
        if (empty($rows)) return 0;
        $place = [];
        $vals = [];
        foreach ($rows as $r) {
            $place[] = "(?, ?)";
            $vals[] = $r['phone'];
            $vals[] = $r['name'];
        }
        $sql = "INSERT IGNORE INTO recipients (phone, name) VALUES " . implode(',', $place);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($vals);
        return $stmt->rowCount();
    }

    public function allIds(): array {
        $stmt = $this->pdo->query("SELECT id FROM recipients");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function count(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM recipients");
        return (int)$stmt->fetchColumn();
    }
}