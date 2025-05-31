FROM dunglas/frankenphp

# The following command installs unzip and cleans up apt cache to reduce image size
# The 'rm -rf /var/lib/apt/lists/*' removes package lists which are no longer needed after installation
# This is a Docker best practice to keep images small and efficient
RUN apt-get update && apt-get install -y unzip && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
	pdo_pgsql \
	intl \
	zip \
	opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV FRANKENPHP_CONFIG="worker /app/public/index.php"
ENV APP_RUNTIME="Runtime\\FrankenPhpSymfony\\Runtime"