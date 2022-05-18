<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Account;
use App\Entity\Instrument;

class InstrumentTest extends TestCase
{
    public function testAccountType(): void
    {
        $i = new Instrument();
        $i->setEusipa(Instrument::EUSIPA_UNDERLYING);
        $this->assertEquals($i->getSupportedAccountTypes(), [ Account::TYPE_CASH ]);
        $i->setEusipa(Instrument::EUSIPA_CFD);
        $this->assertEquals($i->getSupportedAccountTypes(), [ Account::TYPE_MARGIN ]);
    }
}
