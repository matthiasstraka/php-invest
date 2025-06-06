<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: "account_transaction")]
#[ORM\UniqueConstraint(name: "UNQ_account_transaction_id", columns: ["account_id", "transaction_id"])]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "bigint", nullable: true, options: ["unsigned" => true, "comment" => "Unique broker transaction ID"])]
    #[Assert\PositiveOrZero]
    private $transaction_id;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $account;

    #[ORM\Column(type: "datetime")]
    private $time;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $portfolio;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $cash;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $commission;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $tax;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $interest;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true)]
    private $consolidation;

    #[ORM\Column(type: "text", length: 50000, nullable: true)]
    private $notes;

    #[ORM\Column(type: "boolean", nullable: false, options: ["default" => false])]
    private bool $consolidated = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionId(): ?int
    {
        return $this->transaction_id;
    }

    public function setTransactionId(?int $id): self
    {
        $this->transaction_id = $id;

        return $this;
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

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

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

    public function getPortfolio(): ?string
    {
        return $this->portfolio;
    }

    public function setPortfolio(?string $portfolio): self
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    public function getCash(): ?string
    {
        return $this->cash;
    }

    public function setCash(?string $cash): self
    {
        $this->cash = $cash;

        return $this;
    }

    public function getCommission(): ?string
    {
        return $this->commission;
    }

    public function setCommission(?string $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getInterest(): ?string
    {
        return $this->interest;
    }

    public function setInterest(?string $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    public function getConsolidation(): ?string
    {
        return $this->consolidation;
    }

    public function setConsolidation(?string $consolidation): self
    {
        $this->consolidation = $consolidation;

        return $this;
    }

    public function getConsolidated(): ?bool
    {
        return $this->consolidated;
    }

    public function setConsolidated(?bool $consolidated): self
    {
        $this->consolidated = $consolidated;

        return $this;
    }
}
