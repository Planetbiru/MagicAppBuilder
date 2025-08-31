# Use the official PHP image with Apache
FROM php:8.1-apache

# Install required PHP extensions and utilities
RUN apt-get update && apt-get install -y \
    git \
    libsqlite3-dev \
    libzip-dev \
    sqlite3 \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip mysqli pdo pdo_mysql pdo_sqlite \
    && a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy the project files to the container
COPY . /var/www/html

# Set permissions for the project files and enable Apache mod_rewrite
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]
