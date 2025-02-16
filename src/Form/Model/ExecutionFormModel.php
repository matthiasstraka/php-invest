<?php

namespace App\Form\Model;

use App\Entity\Execution;
use App\Entity\Transaction;
use Symfony\Component\Validator\Constraints as Assert;

class ExecutionFormModel
{
    public $time;

    public $account;

    public $instrument;

    public int $direction;

    public $volume;

    public $price;

    public int $type;

    #[Assert\PositiveOrZero]
    public $transaction_id;

    #[Assert\PositiveOrZero]
    public $execution_id;

    public $marketplace;

    public $notes;

    public bool $consolidated;

    #[Assert\NegativeOrZero]
    public $commission;

    public $tax;

    #[Assert\NegativeOrZero]
    public $interest;

    #[Assert\Positive]
    public $exchange_rate = "1";

    #[Assert\Currency]
    public $currency;

    public function populateExecution(Execution $execution)
    {
        $execution->setInstrument($this->instrument);
        $execution->setVolume($this->volume);
        $execution->setPrice($this->price);
        $execution->setExchangeRate($this->exchange_rate);
        $execution->setCurrency($this->currency);
        $execution->setDirection($this->direction);
        $execution->setType($this->type);
        $execution->setExecutionId($this->execution_id);
        $execution->setMarketPlace($this->marketplace);
        $this->populateTransaction($execution->getTransaction());
    }

    public function fromExecution(Execution $execution)
    {
        $this->instrument = $execution->getInstrument();
        $this->volume = $execution->getVolume();
        $this->price = $execution->getPrice();
        $this->exchange_rate = $execution->getExchangeRate();
        $this->currency = $execution->getCurrency();
        $this->direction = $execution->getDirection();
        $this->type = $execution->getType();
        $this->execution_id = $execution->getExecutionId();
        $this->marketplace = $execution->getMarketPlace();
        $this->fromTransaction($execution->getTransaction());
    }
    
    private function populateTransaction(Transaction $transaction)
    {
        $transaction->setTime($this->time);
        $transaction->setAccount($this->account);
        if ($this->direction == 0)
        {
            if ($this->type == Execution::TYPE_DIVIDEND)
            {
                $total = $this->volume * $this->price / $this->exchange_rate;
                $transaction->setCash($total);
            }
            else
            {
                $transaction->setCash(null);
            }
        }
        else
        {
            $total = -1 * $this->direction * $this->volume * $this->price / $this->exchange_rate;
            $transaction->setPortfolio($total);
        }
        if ($this->commission) {
            $transaction->setCommission($this->commission);
        } else {
            $transaction->setCommission(null);
        }
        if ($this->tax !== null) {
            $transaction->setTax($this->tax);
        } elseif($this->instrument->getExecutionTaxRate() != null && $this->direction != 0) {
            // apply execution tax rate of the instrument if no tax provided in the tax field
            $transaction->setTax($this->direction * $total * $this->instrument->getExecutionTaxRate());
        } else {
            $transaction->setTax(null);
        }
        if ($this->interest) {
            $transaction->setInterest($this->interest);
        } else {
            $transaction->setInterest(null);
        }
        $transaction->setTransactionId($this->transaction_id);
        $transaction->setNotes($this->notes);
        $transaction->setConsolidated($this->consolidated);
    }

    private function fromTransaction(Transaction $transaction)
    {
        $this->time = $transaction->getTime();
        $this->account = $transaction->getAccount();
        $this->transaction_id = $transaction->getTransactionId();
        $this->notes = $transaction->getNotes();
        $this->consolidated = $transaction->getConsolidated();

        $this->commission = $transaction->getCommission();
        $this->tax = $transaction->getTax();
        $this->interest = $transaction->getInterest();
    }
}

?>
