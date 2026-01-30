FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip \
    && docker-php-ext-install mysqli

# Install MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copy project files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

EXPOSE 80
