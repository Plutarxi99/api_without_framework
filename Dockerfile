# Используем официальный образ с Apache и PHP
FROM php:8.4-apache

# Установим нужные системные заголовки и соберём расширение pdo_mysql
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      default-libmysqlclient-dev \
 && docker-php-ext-install pdo_mysql \
&& rm -rf /var/lib/apt/lists/*

# добавляем чтобы apache видел где лежит index.php \
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri "s!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
 && sed -ri "s!<Directory /var/www/html>!<Directory ${APACHE_DOCUMENT_ROOT}>!g" /etc/apache2/apache2.conf

RUN a2enmod rewrite

# Установим рабочую директорию (где лежит public/)
WORKDIR /var/www/html
