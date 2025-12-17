FROM php:8.4-cli
LABEL maintainer Matthias Straka

RUN apt-get update -y && \
    apt-get install -y --no-install-recommends npm

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions @composer apcu bcmath intl

WORKDIR /app
COPY . /app

RUN composer install --no-dev
RUN php bin/console doctrine:schema:create
#RUN php bin/console doctrine:fixtures:load -n --group=seeder

VOLUME [ "/app/var" ]

# Setup up local environment with dummy secret
RUN echo "APP_SECRET='DockerSecret'" > /app/.env.local

EXPOSE 8000

WORKDIR /app/public
CMD php -S 0.0.0.0:8000
