# --------- STAGE 1: Build frontend ---------
FROM node:20 AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# --------- STAGE 2: PHP + Apache ---------
FROM php:8.4-apache

# Install system packages + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip mbstring xml exif pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Use port 10000
RUN sed -i 's/Listen 80/Listen 10000/g' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/g' /etc/apache2/sites-available/000-default.conf

# Set Laravel public as root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Allow .htaccess
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app
COPY . .

# Copy built frontend
COPY --from=node_builder /app/public/build ./public/build

# ✅ IMPORTANT: create dummy .env to avoid artisan crash
RUN cp .env.example .env || true

# ✅ Generate app key BEFORE composer scripts run
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
 && php artisan key:generate || true

# ✅ Install dependencies WITHOUT running scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# ✅ Now run Laravel package discovery safely
RUN php artisan package:discover --ansi || true

# Clear caches
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

# Storage link
RUN php artisan storage:link || true

# Ensure directories
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    public/uploads

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache public/uploads

# Expose port
EXPOSE 10000

# Runtime fix (critical)
CMD chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && apache2-foreground