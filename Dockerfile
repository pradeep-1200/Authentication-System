FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git \
    && docker-php-ext-install mysqli

# Install MongoDB and Redis extensions
RUN pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html/

# Install backend dependencies
WORKDIR /var/www/html/backend
RUN composer install

# Set working directory back to root
WORKDIR /var/www/html/

EXPOSE 80
