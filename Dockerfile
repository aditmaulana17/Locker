
# =========================================================
# STAGE 1
# BUILD FRONTEND
# =========================================================

FROM node:22-alpine AS frontend

WORKDIR /app


# ---------------------------------------------------------
# Package files
# ---------------------------------------------------------

COPY package*.json ./


# ---------------------------------------------------------
# Install dependencies
# ---------------------------------------------------------

RUN npm ci


# ---------------------------------------------------------
# Copy frontend source
# ---------------------------------------------------------

COPY resources ./resources
COPY public ./public
COPY vite.config.* ./


# ---------------------------------------------------------
# Build Vite
# ---------------------------------------------------------

ARG VITE_APP_NAME=Locker

ENV VITE_APP_NAME=${VITE_APP_NAME}

RUN npm run build


# =========================================================
# STAGE 2
# LARAVEL PHP-FPM
# =========================================================

FROM php:8.3-fpm AS app

WORKDIR /var/www/html


# ---------------------------------------------------------
# System dependencies
# ---------------------------------------------------------

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


# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ---------------------------------------------------------
# Composer dependencies
# ---------------------------------------------------------

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ---------------------------------------------------------
# Laravel source
# ---------------------------------------------------------

COPY . .


# ---------------------------------------------------------
# PHP configuration
# ---------------------------------------------------------
# Memuat konfigurasi upload dari php.ini project
# ke konfigurasi PHP-FPM di dalam container.
# ---------------------------------------------------------

COPY php.ini /usr/local/etc/php/conf.d/uploads.ini


# ---------------------------------------------------------
# Copy Vite build
# ---------------------------------------------------------

COPY --from=frontend /app/public/build ./public/build


# ---------------------------------------------------------
# Prepare Laravel directories
# ---------------------------------------------------------

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


# ---------------------------------------------------------
# Permissions
# ---------------------------------------------------------

RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


# ---------------------------------------------------------
# Laravel package discovery
# ---------------------------------------------------------

RUN php artisan package:discover --ansi


# ---------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh


# ---------------------------------------------------------
# PHP-FPM
# ---------------------------------------------------------

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]


# =========================================================
# STAGE 3
# NGINX
# =========================================================

FROM nginx:alpine AS nginx

WORKDIR /var/www/html


# ---------------------------------------------------------
# Laravel public directory
# ---------------------------------------------------------

COPY public ./public


# ---------------------------------------------------------
# Fresh Vite build
# ---------------------------------------------------------

COPY --from=frontend /app/public/build ./public/build


# ---------------------------------------------------------
# Nginx configuration
# ---------------------------------------------------------

COPY docker/nginx/default.conf \
     /etc/nginx/conf.d/default.conf


# ---------------------------------------------------------
# Nginx
# ---------------------------------------------------------

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]