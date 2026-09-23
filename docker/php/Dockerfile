# 支持范围 PHP 8.2-8.4（以 backend/composer.json 的 php 约束为准）。
# 默认构建支持范围下限 8.2；验证其它版本：PHP_VERSION=8.4 docker compose up -d --build
ARG PHP_VERSION=8.2
FROM docker.1ms.run/php:${PHP_VERSION}-fpm

ARG NODE_MAJOR=20

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        pdo_mysql \
        mysqli \
        zip \
        simplexml \
        mbstring \
        curl \
        sockets \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_MAJOR}.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g miniprogram-ci \
    && node --version \
    && npm --version \
    && miniprogram-ci --version \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
