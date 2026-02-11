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
    nginx \
    supervisor

# php extensions
RUN docker-php-ext-install intl opcache pdo pdo_pgsql zip

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# copy app
COPY . .

# install vendors
RUN composer install --no-dev --optimize-autoloader --no-interaction

# nginx conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# supervisor conf
COPY docker/supervisord.conf /etc/supervisord.conf

# permissions
RUN mkdir -p var && chown -R www-data:www-data var

# entrypoint (migrations on startup)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 8080

CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
