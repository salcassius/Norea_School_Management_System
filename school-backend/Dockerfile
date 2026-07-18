FROM php:8.2-cli

WORKDIR /var/www

COPY . .

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD php artisan migrate -- force && php-fpm 