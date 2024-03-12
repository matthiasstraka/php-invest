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

Currently, three data sources are implemented:

#### Market Watch
[MarketWatch.com](https://www.marketwatch.com/) requires no special setup to download daily price data.
This is the preferred data source when no expression is used (the asset symbol and country are automatically used to query data).
If price data cannot be downloaded automatically, you can use a custom expression with one of the following formats:
  * `ticker`: Ticker short name (e.g. `aapl` for Apple stock)
  * `countrycode:ticker`: Ticker with a country prefix (e.g. `dx:dax` for the German DAX index)
  * `type:countrycode:ticker`: Ticker with a manual type (e.g. `future::gc00` for Gold futures). Note that the country code is optional.

#### Alphavantage
[alphavantage.co](https://www.alphavantage.co/) requires a free (or paid) API key in order to access price data from this website.
You can [request a key](https://www.alphavantage.co/support/#api-key) yourself. This key needs to be entered in your `.env.local` file (e.g. a line with `ALPHAVANTAGE_KEY=12345`).

In order to use AlphaVantage for downloading price-data, use the `Price datasource expression` field of the asset and enter the following expression with an `AV/` prefix:
```
AV/<symbol>
```
where `<symbol>` is the ticker symbol (e.g. `AAPL` for Apple stock).
It is also possible to use a JSON string in the format
```json
{"provider": "alphavantage", "symbol": "<symbol>"}
```
Please refer to the [AlphaVantage documentation](https://www.alphavantage.co/documentation/#daily) for details on symbol names.

#### Onvista.de
[onvista.de](https://www.onvista.de/) requires no special setup to download daily price data.
In order to use Onvista for downloading price-data, use the `Price datasource expression` field of the asset and enter following JSON expressing with an *onvista instrument id*:
```json
{"provider":"onvista", "idInstrument": <id>}
```
where `<id>` is a number that identifies the instrument (e.g. 86627 for Apple stock).

Currently, the *onvista instrument id* can be found out by analyzing network traffic of your webbrowser by evaluating calls to the *chart_history* API calls.
Search functionality will be added at a later time (feel free to add a PR for this).

There are additional (optional) properties you can set:
  * `idNotation`: ID of the market place
  * `type`: Type like `FUND` or `STOCK` when auto-type detection fails
  * `scale`: Multiplies price data by this value (defaults to 1)

An expression with all optional fields use may look like this:
```json
{"provider":"onvista", "idInstrument": 86627, "type": "STOCK", "idNotation": 253929, "scale": 1}
```