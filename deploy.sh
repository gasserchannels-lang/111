#!/bin/bash

# Pre-deployment checks
echo "Running pre-deployment checks..."

# Check PHP version
php -v

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Run database migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache

# Show current configuration
php artisan about
