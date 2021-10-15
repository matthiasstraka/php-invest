<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Repository\InstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InstrumentRepository::class)
 * @UniqueEntity("ISIN", message="Each ISIN must be unique")
 */
class Instrument
{
    const TYPE_UNDERLYING = 1;
    const TYPE_CFD = 2;
    const TYPE_KNOCKOUT = 3;
    const TYPE_OPTION = 4;
    const TYPE_STRUCTURED = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, unique=true, nullable=true, options={"fixed":true})
     * @Assert\Isin
     */
    private $ISIN;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $EmissionDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $TerminationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Asset")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Underlying;

    /**
     * @ORM\Column(type="smallint")
     */
    private $Type = self::TYPE_UNDERLYING;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true, "comment": "ISO 4217 Code"})
     * @Assert\Currency
     */
    private $Currency;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Issuer;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Notes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getISIN(): ?string
    {
        return $this->ISIN;
    }

    public function setISIN(?string $ISIN): self
    {
        $this->ISIN = $ISIN;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->Currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->Currency = $currency;

        return $this;
    }

    public function __toString(): string
    {
        return $this->Name;
    }

    public function getEmissionDate(): ?\DateTimeInterface
    {
        return $this->EmissionDate;
    }

    public function setEmissionDate(?\DateTimeInterface $EmissionDate): self
    {
        $this->EmissionDate = $EmissionDate;

        return $this;
    }

    public function getTerminationDate(): ?\DateTimeInterface
    {
        return $this->TerminationDate;
    }

    public function setTerminationDate(?\DateTimeInterface $TerminationDate): self
    {
        $this->TerminationDate = $TerminationDate;

        return $this;
    }

    public function getIssuer(): ?string
    {
        return $this->Issuer;
    }

    public function setIssuer(?string $Issuer): self
    {
        $this->Issuer = $Issuer;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->Notes;
    }

    public function setNotes(?string $Notes): self
    {
        $this->Notes = $Notes;

        return $this;
    }

    public function getUnderlying(): ?Asset
    {
        return $this->Underlying;
    }

    public function setUnderlying(?Asset $Underlying): self
    {
        $this->Underlying = $Underlying;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->Type;
    }

    public function getTypeName(): string
    {
        switch ($this->Type) {
            case self::TYPE_UNDERLYING:
                return "Underlying";
            case self::TYPE_CFD:
                return "CFD";
            case self::TYPE_KNOCKOUT:
                return "Knock-Out";
            case self::TYPE_OPTION:
                return "Option";
            case self::TYPE_STRUCTURED:
                return "Structured";
            default:
                return "Unknown";
        }
    }

    public function setType(int $Type): self
    {
        $this->Type = $Type;

        return $this;
    }
}
