<?php

namespace App\Tests;

use App\Entity\Execution;
use App\Entity\Instrument;
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
        $execution->setCurrency("EUR");
        $transaction->setTime(new \DateTime());
        $transaction->setTax(33.6);
        // TODO: Set remaining values to correctly check for total prices

        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $this->assertSame($data->price, $execution->getPrice());
        $this->assertSame($data->volume, $execution->getVolume());
        $this->assertSame($data->currency, $execution->getCurrency());
        $this->assertSame($data->exchange_rate, $execution->getExchangeRate());
        $this->assertSame($data->tax, $transaction->getTax());
        $this->assertSame($data->commission, null);
        $this->assertSame($data->interest, null);

        $execution2 = new Execution();
        $transaction2 = new Transaction();
        $execution2->setTransaction($transaction2);
        $data->populateExecution($execution2);

        $this->assertSame($execution2->getPrice(), $execution->getPrice());
        $this->assertSame($execution2->getVolume(), $execution->getVolume());
        $this->assertSame($execution2->getCurrency(), $execution->getCurrency());
        $this->assertSame($execution2->getExchangeRate(), $execution->getExchangeRate());
        $this->assertSame($transaction2->getTax(), $transaction->getTax());
        $this->assertSame($transaction2->getCommission(), $transaction->getCommission());
        $this->assertSame($transaction2->getInterest(), $transaction->getInterest());
    }
    
    public function testTaxCalculation(): void
    {
        $execution = new Execution();
        $transaction = new Transaction();
        $instrument = new Instrument();
        $execution->setTransaction($transaction);
        $execution->setInstrument($instrument);
        $instrument->setExecutionTaxRate(0.0012); // 0.12 %

        $transaction->setTime(new \DateTime());
        $execution->setPrice(123);
        $execution->setVolume(15);
        $execution->setCurrency("EUR");

        // should keep tax 0
        $transaction->setTax(0);
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $data->populateExecution($execution);
        $this->assertSame($transaction->getTax(), strval(0));

        // should keep the inserted tax value
        $transaction->setTax(-1.23);
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $data->populateExecution($execution);
        $this->assertSame($transaction->getTax(), strval(-1.23));

        // should add calculated tax (open)
        $calculatedTax = is_numeric($transaction->getPortfolio()) && is_numeric($execution->getInstrument()->getExecutionTaxRate()) ? $transaction->getPortfolio() * $execution->getInstrument()->getExecutionTaxRate() : null; // -1845 * 0.0012 = '-2.214'
        $transaction->setTax(null);
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $data->populateExecution($execution);
        $this->assertSame($transaction->getTax(), strval($calculatedTax));

        // should add calculated tax (close)
        $transaction->setTax(null);
        $execution->setDirection(-1);
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $data->populateExecution($execution);
        $this->assertSame($transaction->getTax(), strval($calculatedTax));

        // should add calculated tax (neutral)
        $transaction->setTax(null);
        $execution->setDirection(0);
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);
        $data->populateExecution($execution);
        $this->assertSame($transaction->getTax(), null);
    }
}
