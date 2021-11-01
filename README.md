# Php-Invest
[![Symfony](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml/badge.svg)](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml)

## About
PHP-Invest is a self-hosted stock portfolio tracking software based on PHP/Symfony framework which tracks portfolios across multiple brokers and automatically updates daily stock data from multiple sources.

## Installation
This project is in early development.
Use of production data is not yet recommended as the database schema can change without notice and without proper migration steps.

To install, first clone the git repository and execute the following commands:

* Install dependencies using composer: `composer install --no-dev`
* Create database schema scripts using `php bin/console doctrine:database:create`
* Initialize the database using `php bin/console doctrine:schema:create`
* Optionally, populate initial demo data using `php bin/console doctrine:fixtures:load -n` (requires dev or test environment)

By default, a sqlite database is created. In order to override this behavior, create a copy of `.env` as `.env.local` and modify your configuration.
So far, only standard Symfony configurations are used. Please refer to the symfony/doctrine documentation for details.

In order to use the site, your webserver needs to publish the `php-invest/public` folder.

### Docker
For a quick demo, you can build a docker image using

```docker build -t phpinvest:latest .```

and run a non-persistent demo system using

```docker run -it --rm -p 8000:8000 phpinvest:latest```.

Note that this docker image uses the built-in php webserver and is not suited for a production environment.
