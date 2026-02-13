FROM php:8.4-fpm-alpine

# deps system
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    libpq \
    nginx

# php extensions
RUN docker-php-ext-install intl opcache pdo pdo_pgsql zip

# verify PostgreSQL drivers are installed (PDO + pgsql)
RUN php -m | grep -E 'pdo_pgsql|pgsql' || (php -m && exit 1)

WORKDIR /var/www/html

# copy app
COPY . .

# PHP/FPM logs -> Render stdout/stderr
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-render.ini

ENV APP_ENV=prod
ENV APP_DEBUG=0

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# install vendors
RUN composer install --no-dev --optimize-autoloader --no-interaction

# nginx conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN mkdir -p /run/nginx && chown -R nginx:nginx /run/nginx

# permissions
RUN mkdir -p var && chown -R www-data:www-data var

# entrypoint (migrations on startup)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
