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
    const TYPE_ACCUMULATION = 6;

    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Transaction::class, fetch: "EAGER", cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private $transaction;

    #[ORM\Column(type: "integer", nullable: true, options: ["unsigned" => true, "comment" => "Unique broker execution ID"])]
    #[Assert\PositiveOrZero]
    private $execution_id;

    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $instrument;

    #[ORM\Column(type: "decimal", precision: 12, scale: 6)]
    private string $volume;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, options: ["unsigned" => true])]
    #[Assert\Positive]
    private string $price;

    #[ORM\Column(type: "string", length: 3, options: ["fixed" => true, "comment" => "ISO 4217 Code"])]
    #[Assert\Currency]
    private $currency;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, options: ["unsigned" => true, "default" => "1"])]
    #[Assert\Positive]
    private string $exchange_rate = "1";

    #[ORM\Column(type: "smallint", options: [
        "default" => 1,
        //"check" => "CHECK(direction IN (-1,0,1))"
        ])]
    #[Assert\Choice(choices: [-1,0,1])]
    private int $direction = 1;

    #[ORM\Column(type: "smallint", nullable: false, options: [
        "default" => self::TYPE_MARKET,
        "unsigned" => true,
        //"check" => "CHECK(type BETWEEN 1 AND 5)",
        ])]
    #[Assert\Choice(choices: [
      self::TYPE_MARKET,
      self::TYPE_LIMIT,
      self::TYPE_STOP,
      self::TYPE_EXPIRED,
      self::TYPE_DIVIDEND,
      self::TYPE_ACCUMULATION,
     ])]
    private int $type = self::TYPE_MARKET;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $marketplace;

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getExecutionId(): ?int
    {
        return $this->execution_id;
    }

    public function setExecutionId(?int $execution_id): self
    {
        $this->execution_id = $execution_id;

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

    public function getExchangeRate(): ?string
    {
        return $this->exchange_rate;
    }

    public function setExchangeRate(string $rate): self
    {
        $this->exchange_rate = $rate;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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

    public static function translateType($type): string
    {
        switch ($type) {
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
            case self::TYPE_ACCUMULATION:
                return "Accumulation";
            default:
                return "Unknown";
        }
    }

    public function getTypeName(): string
    {
        return Execution::translateType($this->type);
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

    public function getMarketPlace(): ?string
    {
        return $this->marketplace;
    }

    public function setMarketPlace(?string $marketplace): self
    {
        $this->marketplace = $marketplace;

        return $this;
    }
}
