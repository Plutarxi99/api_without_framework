<?php

// Подключаем автозагрузку/классы вручную
require_once __DIR__.'/../src/db.php';
require_once __DIR__.'/../src/helpers.php';

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

if ($method === 'POST' && $uri === 'api/upload') {
    header("HTTP/1.1 200 qwfqwf");
}

/* Not found */
header("HTTP/1.1 404 Not Found");
echo "Not Found\n";