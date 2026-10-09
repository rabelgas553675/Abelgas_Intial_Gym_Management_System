# ---------- STAGE 1: build frontend (Vite + Tailwind) ----------
FROM node:20 AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build


# ---------- STAGE 2: PHP + Apache ----------
FROM php:8.4-apache

# System packages + PHP extensions (gd is required for QR codes)
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpq-dev libzip-dev libonig-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip mbstring xml exif pcntl gd \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Keep only mpm_prefork (required by mod_php) - fixes "More than one MPM loaded"
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
 && a2enmod mpm_prefork

# Apache: enable rewrite, listen on 10000, serve Laravel's /public folder
RUN a2enmod rewrite \
 && sed -i 's/Listen 80/Listen 10000/g' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Allow .htaccess (Laravel routing)
RUN echo '<Directory /var/www/html/public>' > /etc/apache2/conf-available/laravel.conf \
 && echo '    AllowOverride All' >> /etc/apache2/conf-available/laravel.conf \
 && echo '    Require all granted' >> /etc/apache2/conf-available/laravel.conf \
 && echo '</Directory>' >> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# App code
COPY . .

# Built frontend assets from stage 1
COPY --from=node_builder /app/public/build ./public/build

# Temporary .env so artisan can boot during the build
# (Railway variables override it at runtime)
RUN cp .env.example .env || true

# PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts
RUN php artisan package:discover --ansi || true
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

# Required folders + permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    storage/logs storage/app/public bootstrap/cache public/uploads \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache public/uploads

# Start script (strip Windows line endings so it runs on Linux)
COPY start.sh /usr/local/bin/start.sh
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]