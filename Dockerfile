# Use the official PHP image with Apache
FROM php:8.1-apache

# Install required PHP extensions and utilities, including GD dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libsqlite3-dev \
    libzip-dev \
    sqlite3 \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) zip mysqli pdo pdo_mysql pdo_sqlite gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /var/www/html

# Copy the project files to the container
COPY . /var/www/html

# Set permissions for the project files
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Expose port 80
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]