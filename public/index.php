<?php
// public/index.php

require_once __DIR__.'/../src/bootstrap.php'; // подключает db, helpers и т.д.
require_once __DIR__.'/../src/Router.php';

// подключаем контроллеры вручную
require_once __DIR__.'/../src/controllers/MailerController.php';
require_once __DIR__.'/../src/controllers/UploadController.php';

$mc = new MailerController();
$uc = new UploadController();

$router = new Router();

// Регистрируем маршруты — передаём callables (controller instance + method)
$router->add('GET', '/test', function () {
    respond_json(['answer' => 'test']);
});

$router->add('POST', '/api/upload', [$uc, 'upload']);
$router->add('GET', '/api/mailers', [$mc, 'index']);
$router->add('POST', '/api/mailers', [$mc, 'create']);
$router->add('GET', '/api/mailers/(\d+)', [$mc, 'show']);
$router->add('POST', '/api/mailers/(\d+)/enqueue', [$mc, 'enqueue']);
$router->add('POST', '/api/mailers/(\d+)/send', [$mc, 'send']);
$router->add('GET', '/api/mailers/(\d+)/status', [$mc, 'status']);

// dispatch
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$router->dispatch($method, $uri);
