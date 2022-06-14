<?php

namespace App\Tests;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentTerms;
use App\Service\InstrumentPriceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstrumentPriceServiceTest extends KernelTestCase
{
    protected $asset;
    protected $instrument;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->asset = new Asset();
        $this->asset->setCurrency("USD");

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
        $ips = static::getContainer()->get(InstrumentPriceService::class);

        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $ap->setAsset($this->asset);
        $ap->setOHLC(100, 110, 90, 105);

        $eurusd = 0.9315;

        $ip = $ips->fromAssetPrice($this->instrument, $ap);
        $this->assertIsObject($ip);
        $this->assertEquals($ap->getDate(), $ip->getDate());
        $this->assertEquals($this->instrument, $ip->getInstrument());
        $this->assertEquals(floatval($ap->getOpen()) * $eurusd, $ip->getOpen());
        $this->assertEquals(floatval($ap->getHigh()) * $eurusd, $ip->getHigh());
        $this->assertEquals(floatval($ap->getLow()) * $eurusd, $ip->getLow());
        $this->assertEquals(floatval($ap->getClose()) * $eurusd, $ip->getClose());
    }

    public function testSingleKnockoutSameDate(): void
    {
        $ips = static::getContainer()->get(InstrumentPriceService::class);

        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $ap->setAsset($this->asset);
        $ap->setOHLC(100, 110, 90, 105);
        
        $instrument_ko = new Instrument();
        $instrument_ko->setUnderlying($this->asset);
        $instrument_ko->setEusipa(Instrument::EUSIPA_MINIFUTURE);
        $instrument_ko->setCurrency("USD");

        $terms = new InstrumentTerms();
        $terms->setDate($d);
        $terms->setStrike("10");
        $terms->setRatio("0.1");

        $ip = $ips->fromAssetPrice($instrument_ko, $ap, $terms);
        $this->assertIsObject($ip);
        $this->assertEquals($ap->getDate(), $ip->getDate());
        $this->assertEquals($instrument_ko, $ip->getInstrument());
        $this->assertEquals(0.1 * (floatval($ap->getOpen()) - 10), $ip->getOpen());
        $this->assertEquals(0.1 * (floatval($ap->getHigh()) - 10), $ip->getHigh());
        $this->assertEquals(0.1 * (floatval($ap->getLow()) - 10), $ip->getLow());
        $this->assertEquals(0.1 * (floatval($ap->getClose()) - 10), $ip->getClose());
    }

    public function testSingleKnockoutInterpolatedDate(): void
    {
        $ips = static::getContainer()->get(InstrumentPriceService::class);

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

        $ip = $ips->fromAssetPrice($instrument_ko, $ap, $terms);
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
        $ips = static::getContainer()->get(InstrumentPriceService::class);

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

        $ip = $ips->fromAssetPrices($this->instrument, $prices);
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
}
