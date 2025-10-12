<?php
// migrate.php
require_once __DIR__ . '/src/db.php';

$pdo = getPdo();

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS recipients (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  phone VARCHAR(16) NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY ux_recipients_norm (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mailers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mailer_queue (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  mailer_id BIGINT NOT NULL,
  recipient_id BIGINT NOT NULL,
  status SMALLINT NOT NULL DEFAULT 0,
  last_attempt_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ux_mailer_recipient (mailer_id, recipient_id),
  KEY ix_mailer_status (mailer_id, status),
  CONSTRAINT fk_queue_mailer FOREIGN KEY (mailer_id) REFERENCES mailers(id) ON DELETE CASCADE,
  CONSTRAINT fk_queue_recipient FOREIGN KEY (recipient_id) REFERENCES recipients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

$pdo->exec($sql);
echo "Migration finished.\n";
