FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libssl-dev \
    libpq-dev \
    nano \
    libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY xdebug.ini "${PHP_INI_DIR}/conf.d/"

WORKDIR /var/www/html

CMD ["php-fpm"]