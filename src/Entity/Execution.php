<?php

namespace App\Entity;

use App\Repository\ExecutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ExecutionRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(name="UNQ_external_id", columns={"account_id", "external_id"})
 *     })
 */
class Execution
{
    const TYPE_MARKET = 1;
    const TYPE_LIMIT = 2;
    const TYPE_STOP = 3;
    const TYPE_EXPIRED = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Instrument::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrument;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;
    
    /**
     * @ORM\OneToOne(targetEntity=Transaction::class)
     */
    private $transaction;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $notes;

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
     */
    private int $direction = 1;

    /**
     * @ORM\Column(type="bigint", nullable=true, options={"unsigned": true, "comment": "Unique broker execution ID"})
     * @Assert\PositiveOrZero
     */
    private $external_id;

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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

        return $this;
    }
    
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getExternalId(): ?string
    {
        return $this->external_id;
    }

    public function setExternalId(?string $id): self
    {
        $this->external_id = $id;

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
