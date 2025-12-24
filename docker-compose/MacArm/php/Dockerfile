FROM php:7.4-fpm AS crmeb_php

WORKDIR /var/www

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        gd \
        mbstring \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        sockets \
        zip; \
    pecl install redis-5.3.7; \
    docker-php-ext-enable redis; \
    rm -rf /var/lib/apt/lists/*; \
    printf '%s\n' 'open_basedir=/var:/var/tmp:/tmp' > /usr/local/etc/php/conf.d/open_basedir.ini
