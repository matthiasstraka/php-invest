<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\AssetPrice;

class AssetPriceTest extends TestCase
{
    public function testDateConversion(): void
    {
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2022-04-06 00:00:00');
        $this->assertEquals(19088, AssetPrice::getDateValue($d));
        $this->assertEquals($d, AssetPrice::valueToDate(19088));
    }

    public function testDate(): void
    {
        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $this->assertEquals($d, $ap->getDate());
        $this->assertEquals(0, $ap->getDateValue(\DateTime::createFromFormat("Y-m-d", '1970-01-01')));
        $this->assertEquals(19039, $ap->getDateValue(\DateTime::createFromFormat("Y-m-d", '2022-02-16')));
    }

    public function testVolume(): void
    {
        $ap = new AssetPrice();
        $this->assertEquals(0, $ap->getVolume());
        $ap->setVolume(1234);
        $this->assertEquals(1234, $ap->getVolume());
    }

    public function testOHLC(): void
    {
        $ap = new AssetPrice();
        $ap->setOHLC(1.2,5.7,-2.55,10000);
        $this->assertEquals(1.2, $ap->getOpen());
        $this->assertEquals(5.7, $ap->getHigh());
        $this->assertEquals(-2.55, $ap->getLow());
        $this->assertEquals(10000, $ap->getClose());
        $this->assertEquals('10000', $ap->getClose());
    }
}
