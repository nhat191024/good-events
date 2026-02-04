#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Wait for mounted volumes to be available
sleep 2

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# run migrations
# php artisan migrate --force

# run seeders
# php artisan db:seed --force

# run storage link
php artisan storage:link

# run npm build
echo "building assets..."
npm run build:ssr

# Set proper permissions
echo "Setting permissions..."
chgrp -R www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
chmod -R g+w /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public
find /var/www/html/storage -type d -exec chmod g+s {} +
find /var/www/html/bootstrap/cache -type d -exec chmod g+s {} +

# Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan optimize:clear

# Cache for production
echo "Caching for production..."
# php artisan route:cache
# php artisan view:cache
# php artisan event:cache
php artisan optimize
# php artisan filament:optimize

echo "Laravel application setup completed!"

# Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel.conf
