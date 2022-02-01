<?php

namespace App\Entity;

use App\Repository\ExecutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExecutionRepository::class)]
class Execution
{
    const TYPE_MARKET = 1;
    const TYPE_LIMIT = 2;
    const TYPE_STOP = 3;
    const TYPE_EXPIRED = 4;
    const TYPE_DIVIDEND = 5;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Transaction::class, fetch: "EAGER", cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private $transaction;

    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    private $instrument;

    #[ORM\Column(type: "decimal", precision: 10, scale: 5)]
    private string $volume;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, options: ["unsigned" => true])]
    #[Assert\Positive]
    private string $price;

    #[ORM\Column(type: "smallint", options: [
        "default" => 1,
        //"check" => "CHECK(direction IN (-1,0,1))"
        ])]
    #[Assert\Choice(choices: [-1,0,1])]
    private int $direction = 1;

    #[ORM\Column(type: "smallint", nullable: false, options: [
        "default" => self::TYPE_MARKET,
        //"check" => "CHECK(type BETWEEN 1 AND 5)",
        ])]
    #[Assert\Choice(choices: [
      self::TYPE_MARKET,
      self::TYPE_LIMIT,
      self::TYPE_STOP,
      self::TYPE_EXPIRED,
      self::TYPE_DIVIDEND,
     ])]
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
    
    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(string $volume): self
    {
        $this->volume = $volume;

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
            case self::TYPE_DIVIDEND:
                return "Dividend";
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
