# Php-Invest
[![Symfony](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml/badge.svg)](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml)

## About
PHP-Invest is a self-hosted stock portfolio tracking software based on PHP/Symfony framework which tracks portfolios across multiple brokers and automatically updates daily stock data from multiple sources.

## Status
This project is under active development.
New features will be added with a priority on function over design and tracking trades over analysis.
The following features are already supported and have reached a usable stability:
* Creating/Editing/Deleting assests (e.g. Stocks)
* Creating/Editing/Deleting instrument on assest (e.g. a knock-out derivative on a Stock)
* Creating/Editing/Deleting account (e.g. a Broker account, Demo account)
* Opening/Closing trades of instruments on accounts is possible

Missing features:
* Cash/Consolidation management
* Trade analysis
* Automated trade management (e.g. relative position size/loss warnings)
* Integration of asses price data
* Proper user management

## Installation
This project is in early development.
Use of production data is not yet recommended as the database schema can change without notice and without proper migration steps.
However, tracking of trades has reached a certain stability which will allow entering trades for later analysis.

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
