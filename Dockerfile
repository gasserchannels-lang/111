# Multi-stage build for production optimization
# Stage 1: Build stage
FROM node:18-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production && npm cache clean --force

# Stage 2: PHP dependencies
FROM composer:2.6 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Stage 3: Production stage
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Create non-root user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -D -S -G www www

# Set working directory
WORKDIR /var/www/html

# Copy PHP dependencies from composer stage
COPY --from=composer-stage /app/vendor ./vendor

# Copy application code
COPY . .

# Copy built assets from node stage
COPY --from=node-builder /app/node_modules ./node_modules

# Set proper permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure PHP-FPM
RUN echo "user = www" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "group = www" >> /usr/local/etc/php-fpm.d/www.conf

# Configure OPcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Expose port 80
EXPOSE 80

# Switch to non-root user
USER www

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
