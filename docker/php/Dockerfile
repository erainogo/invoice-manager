FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev libicu-dev libssl-dev zlib1g-dev \
    && docker-php-ext-install pdo_mysql zip intl pcntl \
    && docker-php-ext-enable redis

# install composer
RUN curl -sS https://getcomposer.org/installer | MEMORY_LIMIT=512M php -- --install-dir=/usr/local/bin/ --filename=composer

RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer

# php.ini PHP runtime behavior (memory, file size, execution)
# zz-www.conf FPM worker limits and request concurrency	In production or heavy queue usage ( seperate config file attached using volumes)

# Set PHP.ini values to support large uploads
RUN echo "upload_max_filesize=200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "request_terminate_timeout=300" >> /usr/local/etc/php/conf.d/uploads.ini