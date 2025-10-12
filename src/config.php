<?php

return [
    // DSN для MySQL
    'dsn' => 'mysql:host=db;post=3306;dbname=mailing;charset=utf8mb4',
    'user' => 'mailuser',
    'pass' => 'password',
    'uploadDir' => __DIR__ . '/../storage/uploads',
    'batchSize' => 500,
];
