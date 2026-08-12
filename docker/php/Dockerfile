# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.5.0
ARG NODE_VERSION=24.15.0
ARG NGINX_VERSION=1.27-alpine

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        curl \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl pcntl pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-hongvan.ini

WORKDIR /var/www/html

FROM php-base AS composer-build

COPY BackEnd/composer.json BackEnd/composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader

COPY BackEnd/ ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --classmap-authoritative

FROM node:${NODE_VERSION}-bookworm-slim AS public-build

WORKDIR /build
COPY BackEnd/package.json BackEnd/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY BackEnd/ ./
RUN npm run build

FROM node:${NODE_VERSION}-bookworm-slim AS admin-build

WORKDIR /build
COPY Admin/package.json Admin/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY Admin/ ./
RUN npm run build

FROM php-base AS app

COPY --from=composer-build --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=public-build --chown=www-data:www-data /build/public/build /var/www/html/public/build
COPY --from=admin-build --chown=www-data:www-data /build/dist/hongvan-admin/browser /var/www/html/public/admin/browser
COPY --chown=www-data:www-data docker/php/entrypoint.sh /usr/local/bin/hongvan-entrypoint

RUN chmod 0755 /usr/local/bin/hongvan-entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache public/storage

USER www-data

ENTRYPOINT ["hongvan-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginx:${NGINX_VERSION} AS nginx

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app/public \
    && chown -R nginx:nginx /var/www/html /var/cache/nginx

USER nginx

EXPOSE 8080

ENTRYPOINT ["nginx", "-g", "daemon off;"]
