# Etapa 1: vendor - instalar dependencias con composer + artisan
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y unzip git libzip-dev zip curl \
    && docker-php-ext-install zip

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan package:discover --ansi


# Etapa 2: producción con PHP-FPM + Nginx
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx curl libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar código + dependencias
COPY --from=vendor /app /var/www/html

# Ajustar permisos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar configuración personalizada de nginx (asegúrate que exista el archivo)
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

# Arrancar nginx y php-fpm en foreground para que el contenedor se mantenga activo
CMD ["sh", "-c", "nginx -g 'daemon off;' & php-fpm -F"]
