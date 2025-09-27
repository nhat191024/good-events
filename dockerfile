FROM php:8.4-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    nodejs \
    npm \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    supervisor \
    procps \
    curl \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip pdo_mysql gd bcmath intl pcntl exif \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy Apache configuration
COPY 000-default-redirect.conf /etc/apache2/sites-available/000-default.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Copy and install PHP dependencies
COPY composer.json composer.lock ./
# RUN composer install --optimize-autoloader --no-scripts --no-dev
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
