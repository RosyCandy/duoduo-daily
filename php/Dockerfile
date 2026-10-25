FROM php:8.2-fpm-alpine

RUN apk add --no-cache libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev \
    && docker-php-ext-install -j1 zip pdo_mysql \
    && apk del .build-deps
