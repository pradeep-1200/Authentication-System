FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install PHP extensions (mysqli, mongodb, redis) using the installer script
# This is much faster and uses pre-built binaries compared to 'pecl install'
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions mysqli mongodb redis

# Install system dependencies required for Composer/General usage
RUN apt-get update && apt-get install -y \
    unzip git \
    && rm -rf /var/lib/apt/lists/*

# Copy only composer files first to leverage Docker cache
COPY backend/composer.json backend/composer.lock* /var/www/html/backend/

# Install backend dependencies
# --ignore-platform-reqs: prevents failure if the build environment slightly differs from the lock file requirements (e.g., extensions)
# --no-dev: optimization for production
# --no-scripts: prevents post-install scripts from failing due to environment specificities
WORKDIR /var/www/html/backend
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts --no-interaction

# Copy the rest of the project files
COPY . /var/www/html/

EXPOSE 80
