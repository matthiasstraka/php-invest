<?php

namespace App\Form\Model;

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
}

?>
