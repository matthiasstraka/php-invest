<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\InstrumentTerms;

class InstrumentTermsTest extends TestCase
{
    public function testRatio(): void
    {
        $it = new InstrumentTerms();
        $this->assertEquals($it->getRatio(), 1);

        $it->setRatio(123);
        $this->assertEquals($it->getRatio(), 123);

        $it->setRatio(1);
        $this->assertEquals($it->getRatio(), 1);

        $it->setRatio(null);
        $this->assertEquals($it->getRatio(), 1);
    }
}
