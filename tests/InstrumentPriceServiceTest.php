<?php

namespace App\Tests;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentTerms;
use App\Service\InstrumentPriceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class InstrumentPriceServiceTest extends KernelTestCase
{
    protected $asset;
    protected $asset_price;
    protected $instrument;
    protected $ips;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->ips = static::getContainer()->get(InstrumentPriceService::class);

        $this->asset = new Asset();
        $this->asset->setCurrency("USD");

        $this->asset_price = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $this->asset_price->setDate($d);
        $this->asset_price->setAsset($this->asset);
        $this->asset_price->setOHLC(100, 110, 90, 105);

        $this->instrument = new Instrument();
        $this->instrument->setUnderlying($this->asset);
        $this->instrument->setEusipa(Instrument::EUSIPA_UNDERLYING);
        $this->instrument->setCurrency("EUR");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSingleUnderlying(): void
    {
        $eurusd = 0.9315;

        $ip = $this->ips->fromAssetPrice($this->instrument, $this->asset_price);
        $this->assertIsObject($ip);
        $this->assertEquals($this->asset_price->getDate(), $ip->getDate());
        $this->assertEquals($this->instrument, $ip->getInstrument());
        $this->assertEquals(floatval($this->asset_price->getOpen()) * $eurusd, $ip->getOpen());
        $this->assertEquals(floatval($this->asset_price->getHigh()) * $eurusd, $ip->getHigh());
        $this->assertEquals(floatval($this->asset_price->getLow()) * $eurusd, $ip->getLow());
        $this->assertEquals(floatval($this->asset_price->getClose()) * $eurusd, $ip->getClose());
    }

    public function testSingleKnockoutSameDate(): void
    {
        $instrument_ko = new Instrument();
        $instrument_ko->setUnderlying($this->asset);
        $instrument_ko->setEusipa(Instrument::EUSIPA_MINIFUTURE);
        $instrument_ko->setCurrency("USD");

        $terms = new InstrumentTerms();
        $terms->setDate($this->asset_price->getDate());
        $terms->setStrike("10");
        $terms->setRatio("0.1");

        $ip = $this->ips->fromAssetPrice($instrument_ko, $this->asset_price, $terms);
        $this->assertIsObject($ip);
        $this->assertEquals($this->asset_price->getDate(), $ip->getDate());
        $this->assertEquals($instrument_ko, $ip->getInstrument());
        $this->assertEquals(0.1 * (floatval($this->asset_price->getOpen()) - 10), $ip->getOpen());
        $this->assertEquals(0.1 * (floatval($this->asset_price->getHigh()) - 10), $ip->getHigh());
        $this->assertEquals(0.1 * (floatval($this->asset_price->getLow()) - 10), $ip->getLow());
        $this->assertEquals(0.1 * (floatval($this->asset_price->getClose()) - 10), $ip->getClose());
    }

    public function testSingleKnockoutInterpolatedDate(): void
    {
        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-01 00:00:00');
        $ap->setDate($d);
        $ap->setAsset($this->asset);
        $ap->setOHLC(100, 110, 90, 105);
        
        $instrument_ko = new Instrument();
        $instrument_ko->setUnderlying($this->asset);
        $instrument_ko->setEusipa(Instrument::EUSIPA_MINIFUTURE);
        $instrument_ko->setCurrency("USD");

        $terms = new InstrumentTerms();
        $terms->setDate(\DateTime::createFromFormat("Y-m-d H:i:s", '2021-03-01 00:00:00'));
        $terms->setStrike("10");
        $terms->setRatio("0.1");
        $terms->setInterestRate("0.15"); // 15% pa

        $days = 214; // days between 2021-03-01 and 2021-10-01
        $strike_factor = (1 + 0.15/365.25) ** $days;
        $this->assertGreaterThan(1, $strike_factor);
        $strike = floatval($terms->getStrike()) * $strike_factor;
        $factor = 0.1;

        $ip = $this->ips->fromAssetPrice($instrument_ko, $ap, $terms);
        $this->assertIsObject($ip);
        $this->assertEquals($ap->getDate(), $ip->getDate());
        $this->assertEquals($instrument_ko, $ip->getInstrument());
        $this->assertEquals(bcmul($factor, floatval($ap->getOpen()) - $strike, 4), $ip->getOpen());
        $this->assertEquals(bcmul($factor, floatval($ap->getHigh()) - $strike, 4), $ip->getHigh());
        $this->assertEquals(bcmul($factor, floatval($ap->getLow()) - $strike, 4), $ip->getLow());        
        $this->assertEquals(bcmul($factor, floatval($ap->getClose()) - $strike, 4), $ip->getClose());
    }

    public function testArray(): void
    {
        $ap1 = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap1->setDate($d);
        $ap1->setAsset($this->asset);
        $ap1->setOHLC(100, 110, 90, 105);

        $ap2 = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-13 00:00:00');
        $ap2->setDate($d);
        $ap2->setAsset($this->asset);
        $ap2->setOHLC(1000, 1100, 900, 1050);

        $prices = [$ap1, $ap2];

        $eurusd = 0.9315;

        $ip = $this->ips->fromAssetPrices($this->instrument, $prices);
        $this->assertIsArray($ip);
        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($prices[$i]->getDate(), $ip[$i]->getDate());
            $this->assertEquals($this->instrument, $ip[$i]->getInstrument());
            $this->assertEquals(floatval($prices[$i]->getOpen()) * $eurusd, $ip[$i]->getOpen());
            $this->assertEquals(floatval($prices[$i]->getHigh()) * $eurusd, $ip[$i]->getHigh());
            $this->assertEquals(floatval($prices[$i]->getLow()) * $eurusd, $ip[$i]->getLow());
            $this->assertEquals(floatval($prices[$i]->getClose()) * $eurusd, $ip[$i]->getClose());
        }
    }

    public function testLeverage(): void
    {
        $this->assertEquals(1, $this->ips->computeLeverage($this->instrument, null, null));

        $instrument_cfd = clone $this->instrument;
        $instrument_cfd->setEusipa(Instrument::EUSIPA_CFD);
        $this->assertEquals(1, $this->ips->computeLeverage($instrument_cfd, null, null));

        $instrument_ko = clone $this->instrument;
        $instrument_ko->setEusipa(Instrument::EUSIPA_MINIFUTURE);
        $this->assertEquals(null, $this->ips->computeLeverage($instrument_ko, null, null));
        $this->assertEquals(null, $this->ips->computeLeverage($instrument_ko, $this->asset_price, null));

        $terms = new InstrumentTerms();
        $terms->setDate($this->asset_price->getDate());
        $terms->setStrike("15");
        $terms->setRatio("0.1");
        $this->assertEquals(105/(105-15), $this->ips->computeLeverage($instrument_ko, $this->asset_price, $terms));

        $instrument_ko->setDirection(Instrument::DIRECTION_SHORT);
        $instrument_ko->setEusipa(Instrument::EUSIPA_KNOCKOUT);
        $terms->setStrike("200");
        $this->assertEquals(105/(200 - 105), $this->ips->computeLeverage($instrument_ko, $this->asset_price, $terms));
    }
}
