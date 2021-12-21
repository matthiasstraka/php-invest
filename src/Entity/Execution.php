<?php

namespace App\Entity;

use App\Repository\ExecutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ExecutionRepository::class)
 */
class Execution
{
    const TYPE_MARKET = 1;
    const TYPE_LIMIT = 2;
    const TYPE_STOP = 3;
    const TYPE_EXPIRED = 4;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=Transaction::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $transaction;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private string $amount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, options={"unsigned": true})
     * @Assert\Positive
     */
    private string $price;

    /**
     * @ORM\Column(type="smallint", options={"comment": "Open = 1, Close = -1", "default": 1})
     * @Assert\Choice(choices={-1,1})
     */
    private int $direction = 1;

    /**
     * @ORM\Column(type="smallint", nullable=false, options={"default": self::TYPE_MARKET})
     * @Assert\Choice(choices={
     *   self::TYPE_MARKET,
     *   self::TYPE_LIMIT,
     *   self::TYPE_STOP,
     *   self::TYPE_EXPIRED,
     * })
     */
    private int $type = self::TYPE_MARKET;

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): string
    {
        switch ($this->type) {
            case self::TYPE_MARKET:
                return "Market";
            case self::TYPE_LIMIT:
                return "Limit";
            case self::TYPE_STOP:
                return "Stop";
            case self::TYPE_EXPIRED:
                return "Expired";
            default:
                return "Unknown";
        }
    }

    public function getDirection(): ?int
    {
        return $this->direction;
    }

    public function setDirection(int $direction): self
    {
        $this->direction = $direction;

        return $this;
    }
}
