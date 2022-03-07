<?php

namespace App\Entity;

use App\Entity\Instrument;
use App\Repository\InstrumentTermsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstrumentTermsRepository::class)]
#[ORM\UniqueConstraint(name: "UNQ_terms_instrument_date", columns: ["instrument_id", "date"])]
class InstrumentTerms
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $instrument;

    #[ORM\Column(type: "date", nullable: true)]
    private $date;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $ratio;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $cap;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $strike;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $knock_out;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $bonus_level;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $reverse_level;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4, nullable: true, options: ["unsigned" => true])]
    private $financing_costs;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRatio(): ?string
    {
        return $this->ratio;
    }

    public function setRatio(?string $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getCap(): ?string
    {
        return $this->cap;
    }

    public function setCap(?string $cap): self
    {
        $this->cap = $cap;

        return $this;
    }

    public function getStrike(): ?string
    {
        return $this->strike;
    }

    public function setStrike(?string $strike): self
    {
        $this->strike = $strike;

        return $this;
    }

    public function getKnockOut(): ?string
    {
        return $this->knock_out;
    }

    public function setKnockOut(?string $knock_out): self
    {
        $this->knock_out = $knock_out;

        return $this;
    }

    public function getBonusLevel(): ?string
    {
        return $this->bonus_level;
    }

    public function setBonusLevel(?string $bonus_level): self
    {
        $this->bonus_level = $bonus_level;

        return $this;
    }

    public function getReverseLevel(): ?string
    {
        return $this->reverse_level;
    }

    public function setReverseLevel(?string $reverse_level): self
    {
        $this->reverse_level = $reverse_level;

        return $this;
    }

    public function getFinancingCosts(): ?string
    {
        return $this->financing_costs;
    }

    public function setFinancingCosts(?string $financing_costs): self
    {
        $this->financing_costs = $financing_costs;

        return $this;
    }
}
