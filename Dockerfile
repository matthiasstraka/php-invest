FROM php:8.0-cli
LABEL maintainer Matthias Straka

RUN apt-get update -y
RUN apt-get install -y --no-install-recommends unzip

RUN pecl install apcu && docker-php-ext-enable apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev
RUN php bin/console doctrine:schema:create

VOLUME [ "/app/var" ]

EXPOSE 8000

WORKDIR /app/public
CMD php -S 0.0.0.0:8000
