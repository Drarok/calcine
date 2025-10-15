FROM php:8.4-cli-alpine

WORKDIR /var/www/app

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY ./composer.json ./composer.lock /var/www/app
RUN ["composer", "install"]

COPY . /var/www/app

CMD ["vendor/bin/phpunit"]
