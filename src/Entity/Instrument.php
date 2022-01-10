<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Repository\InstrumentRepository;
use Doctrine\DBAL\Types\Types;
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

    const STATUS_ACTIVE = 0;
    const STATUS_EXPIRED = 1;
    const STATUS_KNOCKED_OUT = 2;
    const STATUS_BARRIER_BREACHED = 3;
    const STATUS_HIDDEN = 255;

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
     * @ORM\ManyToOne(targetEntity="Asset", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $underlying;

    /**
     * @ORM\Column(type="smallint", options={"comment": "EUSIPA / extended class code", "default": self::CLASS_UNDERLYING})
     * @Assert\Choice(choices={
     *  self::CLASS_CAPITAL_PROTECTION,
     *  self::CLASS_YIELD_ENHANCEMENT,
     *  self::CLASS_PARTICIPATION,
     *  self::CLASS_WARRANT,
     *  self::CLASS_KNOCKOUT,
     *  self::CLASS_CONST_LEVERAGE,
     *  self::CLASS_UNDERLYING,
     *  self::CLASS_CFD,
     * })
     */
    private $instrumentClass = self::CLASS_UNDERLYING;

    /**
     * @ORM\Column(type="smallint", options={"default": self::STATUS_ACTIVE})
     * @Assert\Choice(choices={
     *   self::STATUS_ACTIVE,
     *   self::STATUS_EXPIRED,
     *   self::STATUS_KNOCKED_OUT,
     *   self::STATUS_BARRIER_BREACHED,
     *   self::STATUS_HIDDEN,
     * })
     */
    private $status = self::STATUS_ACTIVE;

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
     * @ORM\Column(type="string", length=2048, nullable=true)
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusName(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return "Active";
            case self::STATUS_EXPIRED:
                return "Expired";
            case self::STATUS_KNOCKED_OUT:
                return "Knocked out";
            case self::STATUS_BARRIER_BREACHED:
                return "Barrier Breached";
            case self::STATUS_HIDDEN:
                return "Hidden";
            default:
                return "Unknown";
        }
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
