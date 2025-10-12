# Mailer

Небольшой проект-репозиторий, в котором реализован минимальный API для загрузки списка получателей из CSV и постановки/обработки рассылок через очередь — **без фреймворков**, только нативный PHP.  
Цель проекта — показать, как устроены базовые части фреймворка (router, controllers, repositories, queue) «под капотом».

---

## Что реализовано
- Загрузка CSV с получателями (`recipients`).
- CRUD для рассылок (`mailers`): создать, получить, список.
- Очередь (`mailer_queue`): постановка в очередь (enqueue) и фейковая отправка (send).
- Идемпотентность enqueue через `UNIQUE(mailer_id, recipient_id)` + `INSERT IGNORE`.
- Простой воркер-имитатор: `POST /api/mailers/{id}/send?limit=N` — помечает N записей как отправленные (статус = SEND).
- Статус рассылки: `GET /api/mailers/{id}/status`.
- Минимальный Router + Controllers + Repositories.

---

## Цели (learning goals)
Проект — учебный. Он показывает:
- Front controller + Router: как маршруты попадают в контроллеры.
- Controllers: чистая точка входа HTTP → бизнес-логика.
- Repositories: слой для работы с БД (чистый SQL).
- Queue pattern: табличная очередь (claim / mark).
- Идемпотентность и восстановление после сбоев (requeue stuck).

---

## Требования
- Docker + docker-compose (рекомендуется)
- PHP >= 8.1 (если используешь enum). Для PHP < 8.1 используй класс с константами вместо enum.
- MySQL (контейнер предусмотрён в `docker-compose.yml`).

---

## Структура проекта (важные файлы)
```
public/                 
src/
  bootstrap.php
  Router.php
  controllers/
    MailerController.php
    UploadController.php
  repositories/
    MailerRepository.php
    RecipientRepository.php
    SimpleQueue.php
  import/
    CsvImporter.php
  enums/
    StatusSendEnum.php
  db.php
  helpers.php
migrate.php
Dockerfile
docker-compose.yml
README.md
```

---

## Быстрый старт (Docker)
1. Запустить контейнеры:
```bash
docker-compose up -d --build
```

2. Создать/обновить таблицы:
```bash
docker exec -it mailing-api php migrate.php
```

3. Проверить логи:
```bash
docker exec -it mailing-api tail -n 200 /var/log/apache2/error.log
```

DB настройки (по умолчанию в `docker-compose.yml`):
- DB: `mailing`
- User: `mailuser` / `password`
- Root: `root_password`
- Порт: `3310` (mapping), внутри контейнера хост `db`.

---

## Основные API endpoints

### Загрузка CSV
```
POST /api/upload
form-data: file=@recipients.csv
```
CSV формат:
```
6048764759382,Peter Montgomery
8924115781565,Susan Levy
...
```

Пример curl:
```bash
curl -X POST -F "file=@recipients.csv" http://localhost:8090/api/upload
```

### Создание рассылки
```
POST /api/mailers
Content-Type: application/json
Body: {"title":"Promo","body":"Hello"}
```

### Список рассылок (пагинация)
```
GET /api/mailers?limit=50&page=1
```

### Получить рассылку
```
GET /api/mailers/{id}
```

### Поставить в очередь всех получателей
```
POST /api/mailers/{id}/enqueue
```
Действие: `INSERT IGNORE INTO mailer_queue (mailer_id, recipient_id) SELECT ?, id FROM recipients`

### Фейковая отправка (пометить N как отправленные)
```
POST /api/mailers/{id}/send?limit=100
```
Действие: `UPDATE mailer_queue SET status = 2, last_attempt_at = NOW() WHERE mailer_id = ? AND status = 0 LIMIT N`

### Статус рассылки
```
GET /api/mailers/{id}/status
```
Возвращает: `{ total, queued, sent, failed }`

---

## Полезные команды
- Перезапустить API контейнер:
```bash
docker-compose restart api
```
- Выполнить ad-hoc PHP:
```bash
docker exec -it mailing-api php -r "echo PHP_VERSION;"
```
- Проверить таблицы в БД:
```bash
docker exec -it mailing-db mysql -u mailuser -ppassword -D mailing -e "SHOW TABLES;"
```
