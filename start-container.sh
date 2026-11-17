#!/bin/bash
set -e

echo "Starting Laravel application setup..."

cd /app

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/testing storage/logs bootstrap/cache
chmod -R a+rw storage bootstrap/cache

php artisan storage:link

echo "Clearing and caching configurations..."
php artisan optimize:clear
php artisan optimize

cat > /usr/local/etc/php/conf.d/zz-goodevent.ini <<'EOF'
upload_max_filesize = 64M
post_max_size = 128M
memory_limit = 512M
max_input_time = 300
max_execution_time = 300
max_file_uploads = 20
max_input_vars = 5000
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 30000
opcache.validate_timestamps = 0
EOF

cat > /etc/supervisord-goodevent.conf <<'EOF'
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
pidfile=/tmp/supervisord.pid
loglevel=info

[program:web]
command=docker-php-entrypoint --config /Caddyfile --adapter caddyfile
directory=/app
priority=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /app/artisan queue:work redis --queue=notifications,default,media,messages --sleep=3 --tries=3 --max-time=300 --memory=500 --timeout=300 --rest=1
directory=/app
numprocs=2
autostart=true
autorestart=true
startsecs=0
stopasgroup=true
killasgroup=true
stopwaitsecs=360
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:laravel-scheduler]
command=php /app/artisan schedule:work
directory=/app
autostart=true
autorestart=true
stopwaitsecs=60
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:laravel-ssr]
command=php /app/artisan inertia:start-ssr
directory=/app
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stopwaitsecs=60
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true
EOF

echo "Laravel application setup completed!"

exec supervisord -c /etc/supervisord-goodevent.conf
