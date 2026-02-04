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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files app
COPY . /var/www/html/

# Install backend dependencies
WORKDIR /var/www/html/backend
RUN composer install

# Set working directory back to root
WORKDIR /var/www/html/

EXPOSE 80
