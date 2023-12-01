<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\AssetPrice;

final class AssetPriceTest extends TestCase
{
    public function testDate(): void
    {
        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $this->assertEquals($d, $ap->getDate());
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
