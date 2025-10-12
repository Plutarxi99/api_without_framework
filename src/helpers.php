<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * Установка статус кода и установка заголовка для REST API Json
 *
 * @param array|string|int $data   Данные для сериализации
 * @param int              $status код ответа
 *
 * @return void
 */
#[NoReturn]
function respond_json(array|string|int $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
