# PHP-Invest
[![Symfony](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml/badge.svg)](https://github.com/matthiasstraka/php-invest/actions/workflows/symfony.yml)

## About
PHP-Invest is a self-hosted stock portfolio tracking software based on PHP/Symfony framework which tracks portfolios across multiple brokers and automatically updates daily stock data from multiple sources.

### Motivation
After may unsuccessful searches for existing self-hosted open source stock portfolio tracking systems, the idea for PHP-Invest was born.
Commercial/Free portfolio tracking services often are limited in their functionality and it is not possible to extend them.

### What it is
* Track all trades across broker/account boundaries
* Support trading of derivatives/instruments on assets
* Good support for position size scaling and cost averaging investment strategies
* Basic charting of daily stock prices and derivatives

### What it is not
* PHP-Invest is not intended for day-trading. Price data can only be stored and updated on a day-per-day basis and usually is not available before the day ends.

## Documentation
For usage documentation, see:

[docs/index.md](https://github.com/matthiasstraka/php-invest/blob/main/docs/index.md)

## Status
This project is under active development.
New features will be added with a priority on function over design and tracking trades over analysis.
The following features are already supported and have reached a usable stability:
* Creating/editing/deleting assests (e.g. Stocks, FX, ...)
* Creating/editing/deleting instrument on assest (e.g. a knock-out derivative on a stock)
* Creating/editing/deleting account (e.g. a broker account, demo account)
* Cash/consolidation management for each account
* Opening/closing trades of instruments on accounts is possible, support for dividend payments
* Basic charting support
* Importing asset prices from [MarketWatch.com](https://www.marketwatch.com/) and [alphavantage.co](https://www.alphavantage.co/) via CSV imports
* Adding notes to assets such as current events, news items, etc.

Missing features:
* Trade analysis
* Automated trade management (e.g. relative position size/loss warnings)
* Proper user management
* Proper support for margin accounts

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

### PHP Unit
In order to execute unit tests, you need to prepare your environment and test-database.
Since we populate the database with test-data, it is important to always set up a new database after any update to have predictable auto-increment keys.

```bash
cd <directory where php-invest is cloned>
composer install
bin/console --env=test doctrine:database:drop --force # Only in case there is an old version
bin/console --env=test doctrine:database:create
bin/console --env=test doctrine:schema:create
bin/console --env=test doctrine:fixtures:load -n
bin/phpunit # Runs the actual PHP unit tests
```

## Version updates
When updating the source code on an existing database, the database schema might have changed.
Usually, an upgrade can be performed without migration scripts using standard console commands.
Currently, migration scripts are not maintained as there is no official Release yet.
Please open an [Issue](https://github.com/matthiasstraka/php-invest/issues) if there are problems when migrating your data.
As always, make sure to **backup the database before any upgrade** or migration operation.

The upgrade procedure looks as follows:
```bash
cd <directory where php-invest is cloned>
git pull
composer install --no-dev
bin/console doctrine:schema:update --dump-sql # Optional step to find out what will change (no execution yet)
bin/console doctrine:schema:update --force # Perform the upgrade
```

## Maintainers and Contributions
PHP-Invest is maintained by [Matthias Straka](https://github.com/matthiasstraka).
Contributions and bugfixes are welcome via pull-requests.
