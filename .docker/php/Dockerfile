FROM php:8.4-fpm-alpine

# Install essential dependencies for Symfony
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/symfony

# Configure PHP for development
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Create var directory and set permissions
CMD mkdir -p /var/www/symfony/var && \
    chmod -R 777 /var/www/symfony/var && \
    php-fpm
