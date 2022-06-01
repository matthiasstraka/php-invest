<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Entity\Account;
use App\Repository\InstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstrumentRepository::class)]
#[UniqueEntity("isin", message: "Each ISIN must be unique")]
class Instrument
{
    // see: https://eusipa.org/wp-content/uploads/European_map_20200213_web.pdf
    const EUSIPA_CLASS_CAPITAL_PROTECTION = 11;

    const EUSIPA_CLASS_YIELD_ENHANCEMENT = 12;
    const EUSIPA_DISCOUNT_CERTIFICATE = 1200;
    const EUSIPA_CAPPED_BONUS_CERTIFICATE = 1250;

    const EUSIPA_CLASS_PARTICIPATION = 13;
    const EUSIPA_BONUS_CERTIFICATE = 1320;

    const EUSIPA_CLASS_WARRANTS = 21;
    const EUSIPA_WARRANT = 2100;
    const EUSIPA_SPREAD_WARRANT = 2110;

    const EUSIPA_CLASS_KNOCKOUTS = 22;
    const EUSIPA_KNOCKOUT = 2200;
    const EUSIPA_MINIFUTURE = 2210;

    const EUSIPA_CLASS_CONSTANT_LEVERAGE = 23;
    const EUSIPA_CONSTANT_LEVERAGE = 2300;

    // Extensions to handle all supported instrument classes
    const EUSIPA_UNDERLYING = 0;
    const EUSIPA_CFD = 30;

    const STATUS_ACTIVE = 0;
    const STATUS_EXPIRED = 1;
    const STATUS_KNOCKED_OUT = 2;
    const STATUS_BARRIER_BREACHED = 3;
    const STATUS_HIDDEN = 255;

    const DIRECTION_LONG = 1;
    const DIRECTION_SHORT = -1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 12, unique: true, nullable: true, options: ["fixed" => true])]
    #[Assert\Isin]
    private $isin;

    #[ORM\Column(type: "string")]
    private $name;

    #[ORM\Column(type: "date", nullable: true)]
    private $emissionDate;

    #[ORM\Column(type: "date", nullable: true)]
    private $terminationDate;

    #[ORM\ManyToOne(targetEntity: Asset::class, fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    private $underlying;

    #[ORM\Column(type: "smallint", options: ["comment" => "EUSIPA / extended class code", "default" => self::EUSIPA_UNDERLYING])]
    private $eusipa = self::EUSIPA_UNDERLYING;

    #[ORM\Column(type: "smallint", options: ["default" => self::DIRECTION_LONG])]
    #[Assert\Choice(choices: [
      self::DIRECTION_LONG,
      self::DIRECTION_SHORT,
    ])]
    private $direction = self::DIRECTION_LONG;

    #[ORM\Column(type: "smallint", options: ["default" => self::STATUS_ACTIVE])]
    #[Assert\Choice(choices: [
      self::STATUS_ACTIVE,
      self::STATUS_EXPIRED,
      self::STATUS_KNOCKED_OUT,
      self::STATUS_BARRIER_BREACHED,
      self::STATUS_HIDDEN,
    ])]
    private $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: "string", length: 3, options: ["fixed" => true, "comment" => "ISO 4217 Code"])]
    #[Assert\Currency]
    private $currency;

    #[ORM\Column(type: "string", nullable: true)]
    private $issuer;

    #[ORM\Column(type: "string", length: 2048, nullable: true)]
    private $url;

    #[ORM\Column(type: "text", nullable: true)]
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

    public function getEusipa(): ?int
    {
        return $this->eusipa;
    }

    public function getEusipaName(): string
    {
        switch ($this->eusipa) {
            case self::EUSIPA_UNDERLYING:
                return "Underlying";
            case self::EUSIPA_CFD:
                return "CFD";
            case self::EUSIPA_KNOCKOUT:
                return "Knock-Out";
            case self::EUSIPA_MINIFUTURE:
                return "Mini Future";
            case self::EUSIPA_WARRANT:
                return "Warrant";
            case self::EUSIPA_SPREAD_WARRANT:
                return "Spread Warrant";
            case self::EUSIPA_BONUS_CERTIFICATE:
                return "Bonus Certificate";
            case self::EUSIPA_DISCOUNT_CERTIFICATE:
                return "Discount Certificate";
            case self::EUSIPA_CAPPED_BONUS_CERTIFICATE:
                return "Capped Bonus Certificate";
            case self::EUSIPA_CONSTANT_LEVERAGE:
                return "Constant leverage";
            default:
                return "Unknown";
        }
    }

    public function setEusipa(int $eusipa): self
    {
        $this->eusipa = $eusipa;

        return $this;
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

    public function getDirectionName(): string
    {
        switch ($this->direction) {
            case self::DIRECTION_LONG:
                return "Long";
            case self::DIRECTION_SHORT:
                return "Short";
            default:
                return "Unknown";
        }
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSupportedAccountTypes(): array
    {
        switch ($this->eusipa) {
            case self::EUSIPA_CFD:
                return [ Account::TYPE_MARGIN ];
            default:
                return [ Account::TYPE_CASH ];
        }
    }

    public function hasTerms(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_UNDERLYING:
                return false;
            default:
                return true;
        }
    }

    public function hasRatio(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_UNDERLYING:
                return false;
            default:
                return true;
        }
    }

    public function hasCap(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_CAPPED_BONUS_CERTIFICATE:
            case self::EUSIPA_DISCOUNT_CERTIFICATE:
            case self::EUSIPA_SPREAD_WARRANT:
                return true;
            default:
                return false;
        }
    }

    public function hasStrike(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_WARRANT:
            case self::EUSIPA_SPREAD_WARRANT:
            case self::EUSIPA_KNOCKOUT:
            case self::EUSIPA_MINIFUTURE:
            case self::EUSIPA_CONSTANT_LEVERAGE:
                return true;
            default:
                return false;
        }
    }

    public function hasBarrier(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_BONUS_CERTIFICATE:
            case self::EUSIPA_CAPPED_BONUS_CERTIFICATE:
            case self::EUSIPA_KNOCKOUT:
            case self::EUSIPA_MINIFUTURE:
            case self::EUSIPA_CONSTANT_LEVERAGE:
                return true;
            default:
                return false;
        }
    }

    public function hasFinancing(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_KNOCKOUT:
            case self::EUSIPA_MINIFUTURE:
            case self::EUSIPA_CONSTANT_LEVERAGE:
            case self::EUSIPA_CFD:
                return true;
            default:
                return false;
        }
    }

    public function hasBonusLevel(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_BONUS_CERTIFICATE:
            case self::EUSIPA_CAPPED_BONUS_CERTIFICATE:
                return true;
            default:
                return false;
        }
    }

    public function hasReverseLevel(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_BONUS_CERTIFICATE:
            case self::EUSIPA_CAPPED_BONUS_CERTIFICATE:
                return true;
            default:
                return false;
        }
    }

    public function hasMargin(): bool
    {
        switch ($this->eusipa) {
            case self::EUSIPA_CFD:
                return true;
            default:
                return false;
        }
    }
}
