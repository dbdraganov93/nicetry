FROM php:8.4-fpm-alpine
WORKDIR /app
RUN apk add --no-cache bash git unzip postgresql-dev icu-dev linux-headers \
    && docker-php-ext-install pdo_pgsql intl opcache sockets
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts || true
COPY . .
CMD ["php-fpm"]
