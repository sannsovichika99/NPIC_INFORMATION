FROM php:8.3.0-apache

# Mod Rewrite
RUN a2enmod rewrite

# Get Linux Library
RUN apt-get update -y && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Get Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Get PHP Extension
RUN docker-php-ext-install gettext intl pdo_mysql gd zip

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


# Get NodeJS
COPY --from=node:23.10.0 /usr/local /usr/local
# Get NodeJS
# COPY --from=node:22 /usr/local/bin /usr/local/bin
# Get npm
# COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules

COPY . /var/www/html

WORKDIR /var/www/html


RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN composer install & \
    npm install
