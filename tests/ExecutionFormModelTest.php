<?php

namespace App\Tests;

use App\Entity\Execution;
use App\Entity\Transaction;
use App\Form\Model\ExecutionFormModel;
use PHPUnit\Framework\TestCase;

final class ExecutionFormModelTest extends TestCase
{
    public function testRoundtrip(): void
    {
        $execution = new Execution();
        $transaction = new Transaction();
        $execution->setTransaction($transaction);

        $execution->setPrice(123);
        $execution->setVolume(15);
        $transaction->setTime(new \DateTime());
        $transaction->setTax(33.6);
        // TODO: Set remaining values to correctly check for total prices

        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $this->assertSame($data->price, $execution->getPrice());
        $this->assertSame($data->volume, $execution->getVolume());
        $this->assertSame($data->tax, $transaction->getTax());
        $this->assertSame($data->commission, null);
        $this->assertSame($data->interest, null);

        $execution2 = new Execution();
        $transaction2 = new Transaction();
        $execution2->setTransaction($transaction2);
        $data->populateExecution($execution2);

        $this->assertSame($execution2->getPrice(), $execution->getPrice());
        $this->assertSame($execution2->getVolume(), $execution->getVolume());
        $this->assertSame($transaction2->getTax(), $transaction->getTax());
        $this->assertSame($transaction2->getCommission(), $transaction->getCommission());
        $this->assertSame($transaction2->getInterest(), $transaction->getInterest());
    }
}
