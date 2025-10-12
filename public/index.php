<?php

// Подключаем автозагрузку/классы вручную
require_once __DIR__.'/../src/db.php';
require_once __DIR__.'/../src/helpers.php';

// вспомогательные классы для инкапсулирования логики
require_once __DIR__ . '/../src/repositories/RecipientRepository.php';
require_once __DIR__ . '/../src/repositories/MailerRepository.php';
require_once __DIR__ . '/../src/repositories/SimpleQueue.php';
require_once __DIR__ . '/../src/import/CsvImporter.php';
require_once __DIR__ . '/../src/enums/StatusSendEnum.php';

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

// загрузить
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
    // загружаем файл на сервер
    if (! move_uploaded_file($f['tmp_name'], $storage)) {
        respond_json(['error' => 'Cannot save uploaded file'], 500);
    }

    respond_json(
        [
            'answer' => 'upload',
            new CsvImporter()->importFile($storage)
        ]
    );
}

// создать рассылку
if ($method === 'POST' && $uri === '/api/mailers') {
    if (is_null($data = getJsonBody())) {
        respond_json(['error' => 'Bad request'], 404);
    }

    if (
            ! array_key_exists('title', $data) ||
            ! array_key_exists('body', $data)
    ) {
        respond_json(['error' => 'POST body empty or no key in array'], 404);
    }
    try {
        $mailer_id = new MailerRepository()->create($data['title'], $data['body']);
    } catch (Exception) {
        respond_json(['error' => 'failed create mailer'], 500);
    }

    respond_json(
        new MailerRepository()->get($mailer_id)
    );
}

// получить рассылку по ID
if ($method === 'GET' && preg_match('#^/api/mailers/(\d+)$#', $uri, $mailer_id)) {
    $mailer = new MailerRepository()->get($mailer_id[1]);

    if (empty($mailer)) {
        respond_json(['error' => 'mailer not found'], 404);
    }

    respond_json($mailer);
}

//получить все рассылки
if ($method === 'GET' && $uri === '/api/mailers') {
    // Параметры запроса
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $raw_page = isset($_GET['page']) ? (int)$_GET['page'] : null;
    $raw_offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;

    if ($limit > 100 || $limit < 0) {
        respond_json(['error' => 'limit exceeded'], 400);
    }

    if (! is_null($raw_page) && $raw_page > 0) {
        $page = $raw_page;
        $offset = ($page - 1) * $limit;
    } elseif (! is_null($raw_offset) && $raw_offset >= 0) {
        $offset = $raw_offset;
        $page = (int) floor($offset / $limit) + 1;
    } else {
        $page = 1;
        $offset = 0;
    }

    // Получаем данные
    $repo = new MailerRepository();
    $total = $repo->count();
    $items = $repo->getAll($limit, $offset);

    $pages = $limit > 0 ? (int)ceil($total / $limit) : 1;

    respond_json([
        'data' => $items,
        'meta' => [
            'total' => $total,
            'per_page' => $limit,
            'page' => $page,
            'pages' => $pages,
            'offset' => $offset
        ]
    ]);
}

// поставка в очередь всех
if ($method === 'POST' && preg_match('#^/api/mailers/(\d+)/enqueue$#', $uri, $m)) {
    $mailer_id = (int)$m[1];

    if (empty(new MailerRepository()->get($mailer_id))) {
        respond_json(['error'=>'Mailer not found'],404); 
    }

    respond_json(['ok' => true,'total_in_queue' => new SimpleQueue()->enqueueAll($mailer_id)]);
}

// отправка всем кто в рассылке участвует
if ($method === 'POST' && preg_match('#^/api/mailers/(\d+)/send$#', $uri, $m)) {
    $mailer_id = (int)$m[1];
    $limit = isset($_GET['limit']) && ctype_digit((string)$_GET['limit']) ? (int)$_GET['limit'] : 100;

    if (empty(new MailerRepository()->get($mailer_id)))  {
        respond_json(['error'=>'Mailer not found'],404);
    }

    respond_json(['marked_as_sent' => new SimpleQueue()->sendBatch($mailer_id, $limit)]);
}

// получение статистики
if ($method === 'GET' && preg_match('#^/api/mailers/(\d+)/status$#', $uri, $m)) {
    $mailer_id = (int)$m[1];

    if (empty(new MailerRepository()->get($mailer_id)))  {
        respond_json(['error'=>'Mailer not found'],404);
    }

    respond_json(new SimpleQueue()->status($mailer_id));
}

/* Not found */
respond_json(['answer' => 'No exist route'], 404);
