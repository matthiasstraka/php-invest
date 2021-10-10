# Php-Invest
## About
This is a self-hosted stock portfolio tracking software based on PHP/Symfony framework which tracks portfolios across multiple brokers and automatically updates daily stock data from multiple sources.

## Installation
This project is in early development.
Use of production data is not yet suggested.
The setup process is based on the Symfony framework and works as follows:

* Create database schema scripts using `php bin/console make:migration`
* Initialize the database using `php bin/console doctrine:migrations:execute`
* Populate initial data using `php bin/console doctrine:fixtures:load`
