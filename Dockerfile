FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql bcmath sockets pcntl gd zip opcache

# Copy application source code
COPY . /var/www

# Configure Nginx & Supervisor
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# Set permissions for Entrypoint
RUN chmod +x /var/www/.docker/entrypoint.sh

# Make storage folders writable
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose ports: 80 (Nginx), 8085 (Laravel Reverb WebSockets)
EXPOSE 80 8085

# Define Entrypoint script
ENTRYPOINT ["/var/www/.docker/entrypoint.sh"]
