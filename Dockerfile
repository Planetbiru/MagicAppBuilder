# Stage 1: Builder
# This stage installs all dependencies, including dev dependencies.
FROM php:8.3-apache as builder

# Install system dependencies and PHP extensions required for the application and Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql pdo_sqlite pdo_pgsql mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files and install dependencies
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Production
# This stage creates the final, smaller production image.
FROM php:8.3-apache

# Install required PHP extensions and utilities
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip mysqli pdo pdo_mysql pdo_sqlite pdo_pgsql \
    && a2enmod rewrite

# Copy application files from the builder stage
WORKDIR /var/www/html
COPY --from=builder /var/www/html .

# Copy custom Apache config to handle routing
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html
