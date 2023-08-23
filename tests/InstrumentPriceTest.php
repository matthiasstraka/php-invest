<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\InstrumentPrice;

class InstrumentPriceTest extends TestCase
{
    public function testDate(): void
    {
        $ap = new InstrumentPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $this->assertEquals($d, $ap->getDate());
    }

    public function testOHLC(): void
    {
        $ap = new InstrumentPrice();
        $ap->setOHLC(1.2,5.7,-2.55,10000);
        $this->assertEquals(1.2, $ap->getOpen());
        $this->assertEquals(5.7, $ap->getHigh());
        $this->assertEquals(-2.55, $ap->getLow());
        $this->assertEquals(10000, $ap->getClose());
        $this->assertEquals('10000', $ap->getClose());
    }
}
