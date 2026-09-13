FROM php:8.2-fpm

RUN apt-get update && apt-get install -y libzip-dev unzip \
    && docker-php-ext-install zip pdo pdo_mysql
