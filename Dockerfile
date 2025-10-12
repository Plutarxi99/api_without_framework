# Используем официальный образ с Apache и PHP
FROM php:8.4-apache

# Установим нужные системные заголовки и соберём расширение pdo_mysql
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      default-libmysqlclient-dev \
 && docker-php-ext-install pdo_mysql \

# Установим рабочую директорию (где лежит public/)
WORKDIR /var/www/html
