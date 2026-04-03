FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www
