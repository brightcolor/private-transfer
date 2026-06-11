FROM node:24-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --ignore-platform-req=ext-redis

FROM php:8.3-fpm-alpine AS app
WORKDIR /var/www/html

RUN apk add --no-cache bash icu-dev libzip-dev postgresql-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install intl pcntl pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor vendor
COPY --from=assets --chown=www-data:www-data /app/public/build public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/private-transfer.ini

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

FROM nginx:1.29-alpine AS web
WORKDIR /var/www/html
COPY --from=app /var/www/html /var/www/html
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
