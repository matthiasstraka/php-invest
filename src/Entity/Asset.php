<?php

namespace App\Entity;

use App\Entity\AssetType;
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
     * @ORM\ManyToOne(targetEntity="AssetType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $AssetType;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true, "comment": "ISO 4217 Code"})
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

    public function getAssetType(): ?AssetType
    {
        return $this->AssetType;
    }

    public function setAssetType(?AssetType $asset_type): self
    {
        $this->AssetType = $asset_type;

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
