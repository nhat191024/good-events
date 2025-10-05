#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Wait for mounted volumes to be available
sleep 2

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
echo "Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

#run migrations
php artisan migrate --force

#run npm build
echo "building assets..."
npm run build

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel application setup completed!"

# Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel.conf
