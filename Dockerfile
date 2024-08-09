FROM php:8-apache

RUN apt-get update && \
    apt-get install -y \
    libzip-dev unzip \
    && docker-php-ext-install zip

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
