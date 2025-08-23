FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    default-mysql-client \
    curl \
    gnupg \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql mbstring xml zip gd intl bcmath opcache \
  && pecl install redis || true \
  && docker-php-ext-enable redis || true \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# --- تثبيت Xdebug (لتغطية PHPUnit) ---
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && { \
    echo "xdebug.mode=coverage"; \
    echo "xdebug.start_with_request=no"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
  } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# -----------------------------------------

WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html || true
EXPOSE 9000
CMD ["php-fpm"]
