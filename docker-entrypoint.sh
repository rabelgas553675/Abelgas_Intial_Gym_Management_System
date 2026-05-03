#!/bin/bash
set -e

echo "==> Setting up .env..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Linking storage..."
php artisan storage:link || true

echo "==> Starting Apache..."
exec apache2-foreground