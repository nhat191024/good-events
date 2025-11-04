FROM php:8.4-fpm-alpine3.22

# Set working directory
WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        git \
        supervisor \
        procps \
        curl \
        unzip \
        nodejs \
        npm \
        nginx \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        libwebp \
        freetype \
        python3 \
        make \
        g++ \
    && apk add --no-cache --virtual .php-build-deps \
        autoconf \
        icu-dev \
        libzip-dev \
        zlib-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        oniguruma-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo_mysql gd bcmath intl pcntl exif \
    && apk del .php-build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Nginx
RUN sed -i 's/^user nginx;/user www-data;/' /etc/nginx/nginx.conf \
    && mkdir -p /run/nginx
COPY nginx/default.conf /etc/nginx/http.d/default.conf

# Copy PHP custom configuration
COPY docker-php-custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts

# Copy and install Node dependencies
COPY package.json package-lock.json* ./
RUN npm install

# Copy supervisor configuration
COPY supervisor-laravel.conf /etc/supervisor/conf.d/laravel.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create necessary directories
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache \
    /var/log/supervisor

# Set proper ownership
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
