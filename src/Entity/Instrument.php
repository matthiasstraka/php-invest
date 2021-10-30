<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Repository\InstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InstrumentRepository::class)
 * @UniqueEntity("isin", message="Each ISIN must be unique")
 */
class Instrument
{
    // see: https://eusipa.org/wp-content/uploads/European_map_20200213_web.pdf
    const CLASS_CAPITAL_PROTECTION = 11;
    const CLASS_YIELD_ENHANCEMENT = 12;
    const CLASS_PARTICIPATION = 13;
    const CLASS_WARRANT = 21;
    const CLASS_KNOCKOUT = 22;
    const CLASS_CONST_LEVERAGE = 23;
    // Extensions to handle all supported instrument classes
    const CLASS_UNDERLYING = 0;
    const CLASS_CFD = 30;

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
    private $isin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $emissionDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $terminationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Asset")
     * @ORM\JoinColumn(nullable=false)
     */
    private $underlying;

    /**
     * @ORM\Column(type="smallint", options={"comment": "EUSIPA / extended class code"})
     */
    private $instrumentClass = self::CLASS_UNDERLYING;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, options={"default": 1})
     */
    private $ratio = '1';

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true, "comment": "ISO 4217 Code"})
     * @Assert\Currency
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $issuer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getISIN(): ?string
    {
        return $this->isin;
    }

    public function setISIN(?string $isin): self
    {
        $this->isin = $isin;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getEmissionDate(): ?\DateTimeInterface
    {
        return $this->emissionDate;
    }

    public function setEmissionDate(?\DateTimeInterface $emissionDate): self
    {
        $this->emissionDate = $emissionDate;

        return $this;
    }

    public function getTerminationDate(): ?\DateTimeInterface
    {
        return $this->terminationDate;
    }

    public function setTerminationDate(?\DateTimeInterface $terminationDate): self
    {
        $this->terminationDate = $terminationDate;

        return $this;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): self
    {
        $this->issuer = $issuer;

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

    public function getUnderlying(): ?Asset
    {
        return $this->underlying;
    }

    public function setUnderlying(?Asset $underlying): self
    {
        $this->underlying = $underlying;

        return $this;
    }

    public function getInstrumentClass(): ?int
    {
        return $this->instrumentClass;
    }

    public function getClassName(): string
    {
        switch ($this->instrumentClass) {
            case self::CLASS_UNDERLYING:
                return "Underlying";
            case self::CLASS_CFD:
                return "CFD";
            case self::CLASS_KNOCKOUT:
                return "Knock-Out";
            case self::CLASS_WARRANT:
                return "Warrant";
            case self::CLASS_CAPITAL_PROTECTION:
                return "Capital protection";
            case self::CLASS_YIELD_ENHANCEMENT:
                return "Yield enhancement";
            case self::CLASS_PARTICIPATION:
                return "Participation";
            case self::CLASS_CONST_LEVERAGE:
                return "Constant leverage";
            default:
                return "Unknown";
        }
    }

    public function setInstrumentClass(int $class): self
    {
        $this->instrumentClass = $class;

        return $this;
    }

    public function getRatio(): ?string
    {
        return $this->ratio;
    }

    public function setRatio(string $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
