FROM php:8-apache

RUN apt-get update && \
    apt-get install -y \
    libzip-dev unzip \
    && docker-php-ext-install zip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

ENV XDEBUG_MODE=coverage

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
