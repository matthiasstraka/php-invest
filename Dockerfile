FROM php:8.1-cli
LABEL maintainer Matthias Straka

RUN apt-get update -y && \
    apt-get install -y --no-install-recommends unzip

RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-install bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev
RUN php bin/console doctrine:schema:create
#RUN php bin/console doctrine:fixtures:load -n --group=seeder

VOLUME [ "/app/var" ]

EXPOSE 8000

WORKDIR /app/public
CMD php -S 0.0.0.0:8000
