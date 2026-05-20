FROM php:8.4-fpm AS builder

RUN apt-get update && apt-get install -y \
        bash \
        libicu-dev \
        libzip-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        opcache \
        mbstring \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM builder AS prod

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev

COPY . .

RUN composer dump-autoload --optimize \
    && php bin/console cache:warmup
