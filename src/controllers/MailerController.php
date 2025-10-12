<?php

use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Взаимодействие с рассылкой
 */
class MailerController
{
    private MailerRepository $mr;
    private SimpleQueue $q;

    public function __construct()
    {
        $this->mr = new MailerRepository();
        $this->q  = new SimpleQueue();
    }

    /**
     * Получить все рассылки
     *
     * GET /api/mailers
     *
     * @return void
     */
    #[NoReturn]
    public function index(): void
    {
        $limit = isset($_GET['limit']) && ctype_digit((string)$_GET['limit']) ? (int)$_GET['limit'] : 100;
        $page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) ? (int)$_GET['page'] : 1;

        if ($limit <= 0) {
            $limit = 100;
        }
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $total = $this->mr->count();
        $items = $this->mr->getAll($limit, $offset);

        respond_json(
            [
                'data' => $items,
                'meta' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'page' => $page,
                    'offset' => $offset
                ]
            ]
        );
    }

    /**
     * Получить одну рассылку
     *
     * GET /api/mailers/{id}
     *
     * @param int $id id рассылки
     *
     * @return void
     */
    #[NoReturn]
    public function show(int $id): void
    {
        if (empty($m = $this->mr->get($id))) {
            respond_json(['error' => 'Mailer not found'], 404);
        }

        respond_json($m);
    }

    /**
     * Создать рассылку
     *
     * POST /api/mailers
     *
     * @return void
     * @throws Exception
     */
    #[NoReturn]
    public function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (! is_array($data) || ! isset($data['title']) || ! isset($data['body'])) {
            respond_json(['error' => 'Bad request'], 400);
        }

        respond_json(
            $this->mr->get(
                $this->mr->create(
                    (string)$data['title'],
                    (string)$data['body']
                )
            ),
            201
        );
    }

    /**
     * Добавление всех участников в рассылку
     *
     * POST /api/mailers/{id}/enqueue
     *
     * @param int $id Id рассылки
     *
     * @return void
     */
    #[NoReturn]
    public function enqueue(int $id): void
    {
        if (empty($this->mr->get($id))) {
            respond_json(['error' => 'Mailer not found'], 404);
        }
        $added = $this->q->enqueueAll($id, true);
        respond_json(['ok'=>true,'added'=>$added]);
    }

    /**
     * Сделать отправленные сообщения
     *
     * POST /api/mailers/{id}/send
     *
     * @param  int  $id
     * @return void
     */
    #[NoReturn]
    public function send(int $id): void
    {
        if (empty($this->mr->get($id))) {
            respond_json(['error' => 'Mailer not found'], 404);
        }
        $limit = isset($_GET['limit']) && ctype_digit((string)$_GET['limit']) ? (int)$_GET['limit'] : 100;
        $marked = $this->q->sendBatch($id, $limit);

        respond_json(['marked_as_sent'=>$marked]);
    }

    /**
     * Получение статистки
     *
     * GET /api/mailers/{id}/status
     *
     * @param int $id Id рассылки
     *
     * @return void
     */
    #[NoReturn]
    public function status(int $id): void
    {
        if (empty($this->mr->get($id))) {
            respond_json(['error' => 'Mailer not found'], 404);
        }
        respond_json($this->q->status($id));
    }
}
