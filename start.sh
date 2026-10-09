#!/bin/sh
cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public
chown -R www-data:www-data storage bootstrap/cache

su -s /bin/sh www-data -c 'php artisan config:clear'
su -s /bin/sh www-data -c 'php artisan migrate --force' || echo 'WARNING: migration failed - check your DB_* variables'
su -s /bin/sh www-data -c 'php artisan storage:link' || true

# Laravel scheduler (runs attendance:auto-timeout every 5 minutes)
su -s /bin/sh www-data -c 'php artisan schedule:work' > /dev/null 2>&1 &

# Make sure only one Apache MPM is loaded (fixes AH00534)
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork > /dev/null 2>&1 || true

exec apache2-foreground