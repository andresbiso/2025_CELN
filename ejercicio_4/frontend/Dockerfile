FROM php:8.1-apache

# Apache document root is /var/www/html
# Copy the PHP file into the web root

COPY index.php /var/www/html/index.php

RUN apt-get update && apt-get install -y libpq-dev

# Install extensions
# pgsql for direct DB access

RUN docker-php-ext-install curl pdo pdo_pgsql

# Ensure Apache permissions are correct (usually handled by base image)

RUN chown -R www-data:www-data /var/www/html
RUN chmod 644 /var/www/html/index.php

# Port 80 is exposed by the base image
# EXPOSE 80