FROM php:8.2
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/local/bin
RUN apt update && apt install -y zip git libicu-dev locales
RUN docker-php-ext-install opcache intl pdo_mysql
RUN locale-gen fr_FR.UTF-8
WORKDIR /app
COPY ./app/composer.json .
RUN composer install
COPY ./app .
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]