FROM php:8.4-fpm-alpine
WORKDIR /app
RUN apk add --no-cache bash git unzip postgresql-dev icu-dev libxml2-dev linux-headers \
    && docker-php-ext-install dom pdo_pgsql intl opcache sockets xml
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts || true
COPY . .
CMD ["php-fpm"]
