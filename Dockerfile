FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install PHP extensions (mysqli, mongodb, redis)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions mysqli mongodb redis

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# COPY EVERYTHING FIRST (Overwrites container files with local files)
COPY . /var/www/html/

# ROBUST DEPENDENCY INSTALLATION
WORKDIR /var/www/html/backend

# Force removal of any existing vendor/lock files to prevent Windows/Linux conflicts
RUN rm -rf vendor composer.lock

# Install dependencies freshly in the environment
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction

# PERMISSIONS FIX: Ensure www-data (Apache user) owns everything
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Reset workdir to root
WORKDIR /var/www/html/

EXPOSE 80
