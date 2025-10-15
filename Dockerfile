FROM php:8.4-cli-alpine

RUN apk add --update --no-cache linux-headers $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN ["mkdir", "/var/www/app"]
RUN ["chown", "1001:1001", "/var/www/app"]
WORKDIR /var/www/app

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./composer.lock /var/www/app
RUN ["composer", "install"]

COPY . /var/www/app

USER "1001:1001"
CMD ["vendor/bin/phpunit"]
