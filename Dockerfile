FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && curl -fsSL https://github.com/phpredis/phpredis/archive/refs/tags/6.1.0.tar.gz -o /tmp/phpredis.tar.gz \
    && mkdir -p /usr/src/phpredis \
    && tar -xzf /tmp/phpredis.tar.gz -C /usr/src/phpredis --strip-components=1 \
    && docker-php-ext-install /usr/src/phpredis \
    && rm -rf /usr/src/phpredis /tmp/phpredis.tar.gz /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies are provided by the host volume in development.
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
