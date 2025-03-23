FROM php:8.4-fpm

# Install dependencies including PostgreSQL development libraries
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    zlib1g-dev \
    libpq-dev \
    wget \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    opcache

# Install Composer with a specific version
COPY --from=composer:2.7.1 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www/symfony

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure OPcache for production
RUN echo 'opcache.memory_consumption=256' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.max_accelerated_files=20000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.validate_timestamps=0' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Create a non-root user to run the container (security best practice)
RUN useradd -ms /bin/bash symfony

# Give symfony user access to the Symfony CLI
RUN chmod +x /usr/local/bin/symfony && \
    chown symfony:symfony /usr/local/bin/symfony

# Use the non-root user
USER symfony

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]