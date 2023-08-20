# PHP-Invest documentation

## Definitions
* **Asset**:
A financial asset like stocks, bonds, currency or index.
Assets cannot be traded directly but require an instrument.
Price data can be downloaded for an asset from multiple data sources.
* **Instrument**:
A tradable instrument on an asset (e.g. the underlying stock or a derivative)
* **Account**:
An account/portfolio at a broker (or a virtual portfolio)
* * Cash account: A type of account where you deposit cash that can be used to acquire several instruments
* **User**: A user of the system.
A user can own serveral accounts and track trades across them.
Shared accounts may be added in a future version.

## Assets
### Downloading price data
You can download price data from the internet using multiple data sources.
In order to configure the data source, you need to fill in the `Price datasource expression` field of the asset. If the field is empty, a best guess is made using the asset symbol and country code.

Currently, two data sources are implemented:
* [MarketWatch.com](https://www.marketwatch.com/):
There are no special setup requirements to download daily price data.
This is the preferred data source when no expression is used (the asset symbol and country are automatically used to query data).
If price data cannot be downloaded automatically, you can use a custom expression with one of the following formats:
  * `ticker`: Ticker short name (e.g. `aapl` for Apple stock)
  * `countrycode:ticker`: Ticker with a country prefix (e.g. `dx:dax` for the German DAX index)
* [alphavantage.co](https://www.alphavantage.co/):
You need a free (or paid) API key in order to access price data from this website. You can [request a key](https://www.alphavantage.co/support/#api-key) yourself. This key needs to be entered in your `.env.local` file (e.g. a line with `ALPHAVANTAGE_KEY=12345`).
In order to use AlphaVantage for downloading price-data, use the `Price datasource expression` field of the asset and enter the symbol name prefixed by `AV/` (e.g. `AV/AAPL` for Apple stock). Please refer to the [AlphaVantage documentation](https://www.alphavantage.co/documentation/#daily) for details on symbol names.
