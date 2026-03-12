# ============================================================
# Stage 1: Composer — Install PHP dependencies
# ============================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY auth.json ./
COPY composer.json composer.lock ./
RUN composer install \
    --optimize-autoloader \
    --no-scripts \
    --no-dev \
    --no-cache \
    --ignore-platform-reqs \
    && rm auth.json

# ============================================================
# Stage 2: Node.js — Build frontend assets
# ============================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./
COPY resources/ ./resources/

# Vendor is needed so Vite can resolve CSS files from PHP packages (e.g. filafly/brisk)
COPY --from=composer-builder /app/vendor ./vendor/

RUN npm run build:ssr

# ============================================================
# Stage 3: Runtime — Final production image
# ============================================================
FROM php:8.4-apache

WORKDIR /var/www/html

# Install runtime system dependencies, compile PHP extensions,
# then purge build-time *-dev headers in the same layer to reduce image size
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libicu-dev \
    libmagickwand-dev \
    supervisor \
    procps \
    curl \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo_mysql gd bcmath intl pcntl exif opcache \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get purge -y --auto-remove \
        libzip-dev \
        libpng-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libicu-dev \
        libmagickwand-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy Apache and PHP configuration
COPY 000-default-redirect.conf /etc/apache2/sites-available/000-default.conf
COPY docker-php-custom.ini /usr/local/etc/php/conf.d/custom.ini

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Copy application source code
COPY app/ ./app/
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY lang/ ./lang/
COPY public/ ./public/
COPY resources/ ./resources/
COPY routes/ ./routes/
COPY artisan ./

# Copy PHP vendor from composer stage
COPY --from=composer-builder /app/vendor ./vendor/

# Copy built frontend assets from node stage (overrides public/build & bootstrap/ssr)
COPY --from=node-builder /app/public/build ./public/build/
COPY --from=node-builder /app/bootstrap/ssr ./bootstrap/ssr/

# Copy supervisor configuration
COPY supervisor-laravel.conf /etc/supervisor/conf.d/laravel.conf

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create necessary directories
RUN mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache \
    /var/log/supervisor

# Set proper ownership
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
