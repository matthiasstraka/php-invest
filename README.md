# Php-Invest
## About
This is a self-hosted stock portfolio tracking software based on PHP/Symfony framework which tracks portfolios across multiple brokers and automatically updates daily stock data from multiple sources.

## Installation
This project is in early development.
Use of production data is not yet recommended.
The setup process is based on the Symfony framework and works as follows:

* Create database schema scripts using `php bin/console doctrine:database:create`
* Initialize the database using `php bin/console doctrine:schema:create`
* Populate initial data using `php bin/console doctrine:fixtures:load -n` (requires dev or test environment)

By default, a sqlite database is created. In order to override this behavior, create a copy of `.env` as `.env.local` and modify your configuration.
So far, only standard Symfony configurations are used.

In order to use the site, your webserver needs to publish the `php-invest/public` folder.
