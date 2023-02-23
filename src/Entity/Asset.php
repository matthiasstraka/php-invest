<?php

namespace App\Entity;

use App\Entity\Country;
use App\Entity\Currency;
use App\Repository\AssetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[UniqueEntity("ISIN", message: "Each ISIN must be unique")]
class Asset
{
    const TYPE_STOCK = 1;
    const TYPE_BOND = 2;
    const TYPE_FX = 3;
    const TYPE_COMMODITY = 4;
    const TYPE_INDEX = 5;
    const TYPE_FUND = 6;
    const TYPE_CRYPTO = 7;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 12, unique: true, options: ["fixed" => true])]
    #[Assert\Isin]
    private $ISIN;

    #[ORM\Column(type: "string")]
    private $name;

    #[ORM\Column(type: "string")]
    private $symbol;

    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    private $type = self::TYPE_STOCK;

    #[ORM\Column(type: "string", length: 3, options: ["fixed" => true, "comment" => "ISO 4217 Code"])]
    #[Assert\NotBlank]
    #[Assert\Currency]
    private $currency;

    #[ORM\Column(type: "string", length: 2, nullable: true, options: ["fixed" => true, "comment" => "ISO 3166-1 Alpha-2 code"])]
    #[Assert\Country]
    private $country;

    #[ORM\Column(type: "string", length: 2048, nullable: true)]
    #[Assert\Url]
    private $url;

    #[ORM\Column(type: "string", length: 2048, nullable: true)]
    #[Assert\Url]
    private $irurl;

    #[ORM\Column(type: "string", length: 2048, nullable: true)]
    #[Assert\Url]
    private $newsurl;

    #[ORM\Column(type: "text", length: 50000, nullable: true)]
    private $notes;

    #[ORM\Column(type: "string", nullable: true, options: ["comment" => "MarketWatch.com ticker code"])]
    private $marketwatch;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getISIN(): ?string
    {
        return $this->ISIN;
    }

    public function setISIN(string $ISIN): self
    {
        $this->ISIN = $ISIN;

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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public static function typeNameFromValue(int $type): string
    {
        switch ($type) {
            case self::TYPE_STOCK:
                return "Stock";
            case self::TYPE_BOND:
                return "Bond";
            case self::TYPE_FX:
                return "Foreign Exchange";
            case self::TYPE_COMMODITY:
                return "Commodity";
            case self::TYPE_INDEX:
                return "Index";
            case self::TYPE_FUND:
                return "Fund";
            case self::TYPE_CRYPTO:
                return "Crypto";
            default:
                return "Unknown";
        }
    }

    public function getTypeName(): string
    {
        return self::typeNameFromValue($this->type);
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

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

    public function getIRUrl(): ?string
    {
        return $this->irurl;
    }

    public function setIRUrl(?string $url): self
    {
        $this->irurl = $url;

        return $this;
    }

    public function getNewsUrl(): ?string
    {
        return $this->newsurl;
    }

    public function setNewsUrl(?string $url): self
    {
        $this->newsurl = $url;

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

    public function getMarketWatch(): ?string
    {
        return $this->marketwatch;
    }

    public function setMarketWatch(?string $marketwatch): self
    {
        $this->marketwatch = $marketwatch;

        return $this;
    }

    public function __toString(): string 
    {
        return $this->name;
    }
}
