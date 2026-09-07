# =========================================================
# STAGE 1 - BUILD FRONTEND
# =========================================================

FROM node:22-alpine AS frontend

WORKDIR /app

ARG VITE_APP_NAME=Locker
ENV VITE_APP_NAME=${VITE_APP_NAME}

# Copy dependency files terlebih dahulu
# supaya Docker build cache tetap optimal
COPY package*.json ./

RUN npm ci

# Copy file yang dibutuhkan Vite
COPY resources ./resources
COPY public ./public
COPY vite.config.* ./

# Build frontend production
RUN npm run build


# =========================================================
# STAGE 2 - LARAVEL / PHP-FPM
# =========================================================

FROM php:8.3-fpm

WORKDIR /var/www/html


# =========================================================
# SYSTEM DEPENDENCIES
# =========================================================

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


# =========================================================
# COMPOSER
# =========================================================

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# =========================================================
# INSTALL PHP DEPENDENCIES
# =========================================================

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# =========================================================
# COPY LARAVEL APPLICATION
# =========================================================

COPY . .


# =========================================================
# COPY FRONTEND BUILD
# =========================================================

COPY --from=frontend /app/public/build ./public/build


# =========================================================
# LARAVEL DIRECTORIES
# =========================================================

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


# =========================================================
# PERMISSIONS
# =========================================================

RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


# =========================================================
# LARAVEL PACKAGE DISCOVERY
# =========================================================

RUN php artisan package:discover --ansi


# =========================================================
# ENTRYPOINT
# =========================================================

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


# =========================================================
# PHP-FPM
# =========================================================

EXPOSE 9000

CMD ["php-fpm"]