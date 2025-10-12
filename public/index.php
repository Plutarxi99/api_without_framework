<?php

// Подключаем автозагрузку/классы вручную
require_once __DIR__.'/../src/db.php';
require_once __DIR__.'/../src/helpers.php';

// вспомогательные классы для инкапсулирования логики
require_once __DIR__ . '/../src/repositories/RecipientRepository.php';

// Конфиг
$config = require __DIR__.'/../src/config.php';

// Получаем метод и uri
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Функция для чтения JSON тела
 *
 * @return mixed
 */
function getJsonBody(): mixed
{
    $body = file_get_contents('php://input');
    return $body ? json_decode($body, true) : null;
}

if ($method === 'GET' && $uri === '/test') {
    respond_json(['answer' => 'test']);
}

if ($method === 'POST' && $uri === '/api/upload') {
    // если файла не существует даем ошибку
    if (! isset($_FILES['file'])) {
        respond_json(['answer' => 'No file uploaded'], 400);
    }

    $f = $_FILES['file'];
    // если файл не CSV
    if ($f['type'] !== 'text/csv') {
        respond_json(['answer' => 'File only CSV'], 400);
    }

    $storage = $config['upload_dir'] . '/' . uniqid('csv_', true) . '.csv';;
    // загружаем файл на север
    if (! move_uploaded_file($f['tmp_name'], $storage)) {
        respond_json(['error' => 'Cannot save uploaded file'], 500);
    }
    $repo = new RecipientRepository();
    var_dump($repo);

    respond_json(['answer' => 'upload']);
}

/* Not found */
respond_json(['answer' => 'error'], 404);
