<?php

namespace App\Tests;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Instrument;
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

    public function testSingle(): void
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
