FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    libzip-dev\
    unzip

RUN docker-php-ext-install pdo pdo_mysql bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

EXPOSE 9000

CMD [ "php-fpm" ]
