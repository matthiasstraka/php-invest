<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\InstrumentPrice;

class InstrumentPriceTest extends TestCase
{
    public function testDateConversion(): void
    {
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2022-04-06 00:00:00');
        $this->assertEquals(InstrumentPrice::getDateValue($d), 19088);
        $this->assertEquals(InstrumentPrice::valueToDate(19088), $d);
    }

    public function testDate(): void
    {
        $ap = new InstrumentPrice();
        $d = \DateTime::createFromFormat("Y-m-d H:i:s", '2021-10-12 00:00:00');
        $ap->setDate($d);
        $this->assertEquals($ap->getDate(), $d);
        $this->assertEquals($ap->getDateValue(\DateTime::createFromFormat("Y-m-d", '1970-01-01')), 0);
        $this->assertEquals($ap->getDateValue(\DateTime::createFromFormat("Y-m-d", '2022-02-16')), 19039);
    }

    public function testOHLC(): void
    {
        $ap = new InstrumentPrice();
        $ap->setOHLC(1.2,5.7,-2.55,10000);
        $this->assertEquals($ap->getOpen(), 1.2);
        $this->assertEquals($ap->getHigh(), 5.7);
        $this->assertEquals($ap->getLow(), -2.55);
        $this->assertEquals($ap->getClose(), 10000);
        $this->assertEquals($ap->getClose(), '10000');
    }
}
