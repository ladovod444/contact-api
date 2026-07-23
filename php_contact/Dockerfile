FROM php:8.4-fpm

# 1. Системные зависимости + Supervisor + nano
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    pkg-config \
    git \
    curl \
    unzip \
    procps \
    nano \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        intl \
        xml \
        zip \
        exif \
        gd \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


# 6. Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/symfony
EXPOSE 9000
