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

    public $direction;

    public $amount;

    #[Assert\PositiveOrZero]
    public $price;

    public $type;

    #[Assert\PositiveOrZero]
    public $external_id;

    public $notes;

    #[Assert\PositiveOrZero]
    public $commission;

    public $tax;

    #[Assert\PositiveOrZero]
    public $interest;

    public function populateExecution(Execution $execution)
    {
        $execution->setAmount($this->amount);
        $execution->setPrice($this->price);
        $execution->setDirection($this->direction);
        $execution->setType($this->type);
        $this->populateTransaction($execution->getTransaction());
    }

    public function fromExecution(Execution $execution)
    {
        $this->amount = $execution->getAmount();
        $this->price = $execution->getPrice();
        $this->direction = $execution->getDirection();
        $this->type = $execution->getType();
        $this->fromTransaction($execution->getTransaction());
    }
    
    private function populateTransaction(Transaction $transaction)
    {
        $transaction->setTime($this->time);
        $transaction->setAccount($this->account);
        $transaction->setInstrument($this->instrument);
        if ($this->direction == 0)
        {
            $total = $this->amount * $this->price;
            $transaction->setDividend($total); // TODO: Currency conversion
        }
        else
        {
            $total = -1 * $this->direction * $this->amount * $this->price;
            $transaction->setPortfolio($total); // TODO: Currency conversion
        }
        if ($this->commission) {
            $transaction->setCommission(-1 * $this->commission);
        }
        if ($this->tax) {
            $transaction->setTax(-1 * $this->tax);
        }
        if ($this->interest) {
            $transaction->setInterest(-1 * $this->interest);
        }
        $transaction->setExternalId($this->external_id);
        $transaction->setNotes($this->notes);
    }

    private function fromTransaction(Transaction $transaction)
    {
        $this->time = $transaction->getTime();
        $this->account = $transaction->getAccount();
        $this->instrument = $transaction->getInstrument();
        $this->external_id = $transaction->getExternalId();
        $this->notes = $transaction->getNotes();

        if ($transaction->getCommission()) {
            $this->commission = -1 * $transaction->getCommission();
        }

        if ($transaction->getTax()) {
            $this->tax = -1 * $transaction->getTax();
        }

        if ($transaction->getInterest()) {
            $this->interest = -1 * $transaction->getInterest();
        }
    }
}

?>
