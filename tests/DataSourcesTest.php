<?php

namespace App\Tests;

use App\Entity\Asset;
//use App\Service\DataSources\Alphavantage;
//use App\Service\DataSources\Marketwatch;
use App\Service\DataSources\Onvista;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DataSourcesTest extends TestCase
{
    public function testOnvista(): void
    {
        $mockResponse = <<<JSON
{
    "isoCurrency": "USD",
    "datetimeLast": [1665403200, 1665489600],
    "first": [5, 6],
    "high": [100, 200],
    "low": [1, 2],
    "last": [10, 20],
    "volume": [11, 22]
}
JSON;
        $httpClient = new MockHttpClient([
            new MockResponse($mockResponse, ['http_code' => 200, 'response_headers' => ['Content-Type: application/json']])
        ]);
        $source = new Onvista($httpClient);

        $appl = new Asset();
        $appl->setName("Apple Inc.");
        $appl->setISIN("US0378331005");
        $appl->setSymbol("AAPL");
        $appl->setType(Asset::TYPE_STOCK);
        $appl->setCurrency("USD");
        $appl->setCountry("US");

        $appl->setPriceDataSource('{"provider": "other"}');
        $this->assertSame($source->supports($appl), false);

        $appl->setPriceDataSource('{"provider": "onvista"}');
        $this->assertSame($source->supports($appl), false);

        $appl->setPriceDataSource('{"provider": "onvista", "idInstrument": 86627}');
        $this->assertSame($source->supports($appl), true);

        $prices = $source->getPrices($appl,
            \DateTime::createFromFormat('U', 1665403200),
            \DateTime::createFromFormat('U', 1665489500));
        $this->assertSame(count($prices), 1);
        $this->assertSame($prices[0]->getOpen(), '5');
        $this->assertSame($prices[0]->getHigh(), '100');
        $this->assertSame($prices[0]->getLow(), '1');
        $this->assertSame($prices[0]->getClose(), '10');
        $this->assertSame($prices[0]->getVolume(), 11);
    }
}
