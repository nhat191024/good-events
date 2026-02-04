FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    nodejs \
    npm \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libicu-dev \
    libmagickwand-dev \
    supervisor \
    procps \
    curl \
    zip\
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo_mysql gd bcmath intl pcntl exif opcache \
    # Install Imagick via PECL
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP custom configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy auth.json for private repositories
COPY auth.json /var/www/html/

# Copy and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-scripts --no-dev

# Remove auth.json after installing dependencies
RUN rm /var/www/html/auth.json

# Copy and install Node dependencies
COPY package.json package-lock.json* ./
RUN npm install

# Copy supervisor configuration
COPY docker/supervisor.conf /etc/supervisor/conf.d/laravel.conf

# Copy entrypoint script
COPY docker/docker-entrypoint.sh /usr/local/bin/
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

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
