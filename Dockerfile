FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ ./resources/
RUN npm run build

FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    postgresql-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite intl gd opcache mbstring

RUN echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.memory_consumption=256' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.max_accelerated_files=20000' >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo 'opcache.validate_timestamps=0' >> /usr/local/etc/php/conf.d/opcache.ini

FROM base AS production
WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 9000
USER www-data
CMD ["php-fpm"]
