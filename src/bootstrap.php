<?php

// общие вещи и зависимости
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// репозитории и утилиты
require_once __DIR__ . '/repositories/MailerRepository.php';
require_once __DIR__ . '/repositories/RecipientRepository.php';
require_once __DIR__ . '/repositories/SimpleQueue.php';
require_once __DIR__ . '/import/CsvImporter.php';
require_once __DIR__ . '/enums/StatusSendEnum.php';

// Router
require_once __DIR__ . '/Router.php';
