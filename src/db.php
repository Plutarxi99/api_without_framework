<?php

$config = require __DIR__ . '/config.php';

/**
 * Получение или установление подключения к бд
 *
 * @return PDO
 */
function getPdo(): PDO
{
    static $pdo = null;
    global $config;
    if (is_null($pdo)) {
        $pdo = new PDO(
            $config['dsn'],
            $config['user'],
            $config['pass'],
            [
                // обработка ошибок
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}
