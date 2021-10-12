<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\AssetPrice;

class AssetPriceTest extends TestCase
{
    public function testDate(): void
    {
        $ap = new AssetPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $this->assertEquals($ap->getDate(), $d);
    }

    public function testVolume(): void
    {
        $ap = new AssetPrice();
        $this->assertEquals($ap->getVolume(), 0);
        $ap->setVolume(1234);
        $this->assertEquals($ap->getVolume(), 1234);
    }

    public function testOHLC(): void
    {
        $ap = new AssetPrice();
        $ap->setOHLC(1.2,5.7,-2.55,10000);
        $this->assertEquals($ap->getOpen(), 1.2);
        $this->assertEquals($ap->getHigh(), 5.7);
        $this->assertEquals($ap->getLow(), -2.55);
        $this->assertEquals($ap->getClose(), 10000);
        $this->assertEquals($ap->getClose(), '10000');
    }
}
