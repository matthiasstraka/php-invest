<?php

namespace App\Tests;

use App\Entity\Asset;
use App\Service\DataSources\Alphavantage;
//use App\Service\DataSources\Marketwatch;
use App\Service\DataSources\Onvista;
use App\Service\DataSources\WienerBoerse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DataSourcesTest extends TestCase
{
    public function testOnvistaParser() : void
    {
        $this->assertSame(Onvista::ParseDatasourceString(null), null);
        $this->assertSame(Onvista::ParseDatasourceString(""), null);
        $this->assertSame(Onvista::ParseDatasourceString("MW/AAPL"), null);
        $config = ['provider'=>'onvista', 'idInstrument' => '12345'];
        $this->assertSame(Onvista::ParseDatasourceString("OV/12345"), $config);
        $this->assertSame(Onvista::ParseDatasourceString('{"provider":"onvista","idInstrument":"12345"}'), $config);
        $config = ['provider' => 'onvista', 'idInstrument' => '12345', 'idNotation' => 678];
        $this->assertSame(Onvista::ParseDatasourceString("OV/12345@678"), $config);
        $config = ['provider' => 'onvista', 'idInstrument' => 'EURUSD', 'idNotation' => 678];
        $this->assertSame(Onvista::ParseDatasourceString("OV/EURUSD@678"), $config);
    }

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

    public function testAlphavantage(): void
    {
        $this->assertSame(Alphavantage::ParseDatasourceString(null), null);
        $this->assertSame(Alphavantage::ParseDatasourceString(""), null);
        $this->assertSame(Alphavantage::ParseDatasourceString("MW/AAPL"), null);
        $aapl = ['provider'=>'alphavantage', 'symbol' => 'AAPL'];
        $this->assertSame(Alphavantage::ParseDatasourceString("AV/AAPL"), $aapl);
        $msft = ['provider'=>'alphavantage', 'symbol' => 'MSFT'];
        $this->assertSame(Alphavantage::ParseDatasourceString('{"provider":"alphavantage","symbol":"MSFT"}'), $msft);
        $sie = ['provider'=>'alphavantage', 'symbol' => 'SIE.DEX'];
        $this->assertSame(Alphavantage::ParseDatasourceString("AV/SIE.DEX"), $sie);
    }

    public function testWienerBoerseParser(): void
    {
        $this->assertNull(WienerBoerse::ParseDatasourceString(null));
        $this->assertNull(WienerBoerse::ParseDatasourceString('OV/12345'));
        $this->assertSame(
            ['provider' => 'wienerboerse', 'idNotation' => 281309056],
            WienerBoerse::ParseDatasourceString('WB/281309056')
        );
        $this->assertSame(
            ['provider' => 'wienerboerse', 'idNotation' => 281309056, 'idSecurityType' => 2, 'instrumentDataNeeded' => 'Bond'],
            WienerBoerse::ParseDatasourceString(
                '{"provider":"wienerboerse","idNotation":"281309056","idSecurityType":2,"instrumentDataNeeded":"Bond"}'
            )
        );
        $this->assertNull(WienerBoerse::ParseDatasourceString('{"provider":"wienerboerse","idNotation":0}'));
    }

    public function testWienerBoerse(): void
    {
        $responseBody = <<<'JSON'
{"reqType":"TSStimeSeries","data":[
    {"DATETIME_LAST":1735776000000,"FIRST":87.12,"HIGH":87.16,"LOW":87.12,"LAST":87.16,"TOTAL_VOLUME":0},
    {"DATETIME_LAST":1735862400000,"FIRST":88.12,"HIGH":88.16,"LOW":88.12,"LAST":88.16,"TOTAL_VOLUME":30000}
]}
JSON;
        $requestOptions = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requestOptions, $responseBody): MockResponse {
            $requestOptions = $options;
            return new MockResponse($responseBody, [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ]);
        });
        $source = new WienerBoerse($httpClient);

        $asset = new Asset();
        $asset->setPriceDataSource('WB/281309056');
        $startDate = new \DateTimeImmutable('2025-01-02T00:00:00+00:00');
        $endDate = new \DateTimeImmutable('2025-01-02T23:59:59+00:00');

        $this->assertTrue($source->supports($asset));
        $prices = $source->getPrices($asset, $startDate, $endDate);

        $this->assertCount(1, $prices);
        $this->assertSame('87.12', $prices[0]->getOpen());
        $this->assertSame('87.16', $prices[0]->getHigh());
        $this->assertSame('87.12', $prices[0]->getLow());
        $this->assertSame('87.16', $prices[0]->getClose());
        $this->assertSame(0, $prices[0]->getVolume());
        $this->assertSame('2025-01-02', $prices[0]->getDate()->format('Y-m-d'));
        $this->assertSame('2025-01-02T00:00:00.000Z', $requestOptions['query']['DATETIME_TZ_START_RANGE']);
        $this->assertSame('2025-01-02T23:59:59.000Z', $requestOptions['query']['DATETIME_TZ_END_RANGE']);
        $this->assertSame(281309056, $requestOptions['query']['ID_NOTATION']);
        $this->assertSame('1D', $requestOptions['query']['GRANULARITY']);
    }
}
