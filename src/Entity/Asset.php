<?php

namespace App\Entity;

use App\Entity\AssetType;
use App\Entity\Country;
use App\Entity\Currency;
use App\Repository\AssetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssetRepository::class)
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
     * @ORM\ManyToOne(targetEntity="Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Currency;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
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

    public function getCurrency(): ?Currency
    {
        return $this->Currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->Currency = $currency;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->Country;
    }

    public function setCountry(?Country $country): self
    {
        $this->Country = $country;

        return $this;
    }
}
