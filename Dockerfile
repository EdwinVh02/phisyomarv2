# Etapa vendor con php-cli para composer y artisan
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y unzip git libzip-dev zip \
    && docker-php-ext-install zip

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan package:discover --ansi

# Etapa final
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx curl libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "service nginx start && php-fpm"]
