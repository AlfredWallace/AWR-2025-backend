FROM dunglas/frankenphp

RUN install-php-extensions \
	pdo_pgsql \
	intl \
	zip \
	opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

