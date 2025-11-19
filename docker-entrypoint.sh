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
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan optimize:clear

# Cache for production
echo "Caching for production..."
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

# fix nginx tmp dir permission
echo "Fixing nginx client_body_temp_path permissions..."
mkdir -p /var/lib/nginx/tmp/client_body
chown -R www-data:www-data /var/lib/nginx/
chmod -R 755 /var/lib/nginx/

echo "Laravel application setup completed!"

# Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel.conf
