FROM php:7.4-fpm-alpine

# Installing lumen requirements
RUN docker-php-ext-install pdo pdo_mysql

# Getting composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY ./ /app
WORKDIR /app

RUN composer install
