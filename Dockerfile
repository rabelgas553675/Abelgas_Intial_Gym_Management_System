# Base Image
FROM php:8.2-apache

# Set Composer memory
ENV COMPOSER_MEMORY_LIMIT=-1

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libicu-dev \
    zip \
    && docker-php-ext-install \
    pdo pdo_mysql pdo_pgsql zip mbstring xml bcmath intl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite

# Change Apache port to 10000 (Render default)
RUN sed -i 's/Listen 80/Listen 10000/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/g' /etc/apache2/sites-available/000-default.conf

# Set Laravel public as document root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Allow .htaccess
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Create .env (IMPORTANT)
RUN cp .env.example .env || true

# Install PHP dependencies (SAFE MODE)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate app key + run Laravel setup
RUN php artisan key:generate || true \
    && php artisan package:discover || true

# Install frontend dependencies
RUN npm install && npm run build

# Clear caches
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear || true

# Storage link
RUN php artisan storage:link || true

# Fix permissions
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    public/uploads \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads \
    && chmod -R 775 storage bootstrap/cache public/uploads

# (Optional) Run migrations
RUN php artisan migrate --force || true

# Expose port
EXPOSE 10000

# Start Apache
CMD ["apache2-foreground"]