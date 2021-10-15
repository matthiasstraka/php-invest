<?php

namespace App\Entity;

use App\Entity\Country;
use App\Entity\Currency;
use App\Repository\AssetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AssetRepository::class)
 * @UniqueEntity("ISIN", message="Each ISIN must be unique")
 */
class Asset
{
    const TYPE_STOCK = 1;
    const TYPE_BOND = 2;
    const TYPE_FX = 3;
    const TYPE_COMMODITY = 4;
    const TYPE_INDEX = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12, unique=true, options={"fixed":true})
     * @Assert\Isin
     */
    private $ISIN;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Symbol;

    /**
     * @ORM\Column(type="integer")
     */
    private $Type = self::TYPE_STOCK;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true, "comment": "ISO 4217 Code"})
     * @Assert\NotBlank
     * @Assert\Currency
     */
    private $Currency;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"fixed":true, "comment":"ISO 3166-1 Alpha-2 code"})
     * @Assert\Country
     */
    private $Country;

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
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->Symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->Symbol = $symbol;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->Type;
    }

    public function getTypeName(): string
    {
        switch ($this->Type) {
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
            default:
                return "Unknown";
        }
    }

    public function setType(?int $type): self
    {
        $this->Type = $type;

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

    public function getCountry(): ?string
    {
        return $this->Country;
    }

    public function setCountry(?string $country): self
    {
        $this->Country = $country;

        return $this;
    }

    public function __toString(): string 
    {
        return $this->Name;
    }
}
