FROM php:8-apache

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
