# api_without_framework

# Запустить в фоне БАзу данных для работы с ней
docker-compose up -d

# Установка миграций
docker compose exec api sh -c "php migrate.php"