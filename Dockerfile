FROM php:8.2-cli AS vendor

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y unzip git libzip-dev zip curl \
    && docker-php-ext-install zip

WORKDIR /app

# Instalar composer manualmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan package:discover --ansi
