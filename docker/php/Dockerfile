FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache postgresql-client postgresql-dev libzip-dev icu-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev git unzip $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql zip intl gd bcmath pcntl opcache exif \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
WORKDIR /var/www/html

FROM base AS dev
COPY docker/php/opcache-dev.ini /usr/local/etc/php/conf.d/zz-opcache.ini

FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

FROM base AS vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

FROM base AS prod
COPY docker/php/opcache-prod.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /build/public/build ./public/build
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data

FROM nginx:1.27-alpine AS nginx-prod
COPY docker/nginx/prod.conf /etc/nginx/conf.d/default.conf
COPY --from=prod /var/www/html/public /var/www/html/public
