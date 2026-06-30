FROM php:8.4-fpm-bookworm
WORKDIR /app
RUN apt-get update \
    && apt-get install -y --no-install-recommends bash git unzip libpq-dev libicu-dev libxml2-dev curl ca-certificates gnupg procps \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL https://repo.nordvpn.com/gpg/nordvpn_public.asc | gpg --dearmor -o /etc/apt/keyrings/nordvpn.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nordvpn.gpg] https://repo.nordvpn.com/deb/nordvpn/debian stable main" > /etc/apt/sources.list.d/nordvpn.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nordvpn \
    && docker-php-ext-install dom pdo_pgsql intl opcache sockets xml \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts || true
COPY . .
CMD ["php-fpm"]
