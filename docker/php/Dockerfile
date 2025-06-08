FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev libicu-dev libssl-dev zlib1g-dev \
    && docker-php-ext-install pdo_mysql zip intl pcntl \
    && docker-php-ext-enable redis

# install composer
RUN curl -sS https://getcomposer.org/installer | MEMORY_LIMIT=512M php -- --install-dir=/usr/local/bin/ --filename=composer

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer