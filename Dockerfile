# Etapa 1: Construcción de dependencias de Laravel
FROM composer:2.6 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .

# Etapa 2: Imagen final con PHP y Nginx
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    nginx curl libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar Composer desde la primera etapa
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copiar dependencias instaladas y código
WORKDIR /var/www/html
COPY --from=vendor /app ./

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Copiar configuración de Nginx
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Exponer puertos
EXPOSE 80

# Iniciar PHP-FPM y Nginx
CMD service nginx start && php-fpm
