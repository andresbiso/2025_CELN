FROM php:8.1-fpm

# This variant contains PHP's FastCGI Process Manager (FPM)⁠, which is the recommended FastCGI implementation for PHP.
# In order to use this image variant, some kind of reverse proxy (such as NGINX, Apache, or other tool which speaks the FastCGI protocol) will be required

FROM php:8.1-fpm

# Install required dependencies
RUN apt-get update && apt-get install -y libpq-dev nginx supervisor

# Install PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Prepare working directory
WORKDIR /var/www/html
COPY index.php /var/www/html/index.php
RUN chown -R www-data:www-data /var/www/html
RUN chmod 644 /var/www/html/index.php

# Copy configurations
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Fix permissions
RUN chown -R www-data:www-data /etc/nginx/nginx.conf
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php

# Expose Nginx port
EXPOSE 80

# Start both services using supervisor
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]


