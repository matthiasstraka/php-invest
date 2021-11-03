<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    const TYPE_INSTRUMENT = 1;
    const TYPE_COMMISSION = 2;
    const TYPE_TAX = 3;
    const TYPE_SWAP = 4;
    const TYPE_CASH = 5;
    const TYPE_CONSOLIDATION = 6;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity=Execution::class)
     */
    private $execution;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\Column(type="smallint", options={"default": self::TYPE_INSTRUMENT})
     * @Assert\Choice(choices={
     *   self::TYPE_INSTRUMENT,
     *   self::TYPE_COMMISSION,
     *   self::TYPE_TAX,
     *   self::TYPE_SWAP,
     *   self::TYPE_CASH,
     *   self::TYPE_CONSOLIDATION,
     * })
     */
    private $type;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getExecution(): ?Execution
    {
        return $this->execution;
    }

    public function setExecution(?Execution $execution): self
    {
        $this->execution = $execution;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
