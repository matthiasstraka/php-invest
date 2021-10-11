<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Type\DateKey;
use App\Repository\AssetPriceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssetPriceRepository::class)
 */
class AssetPrice
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Asset", cascade={"all"})
     */
    private $Asset;

    /**
     * @ORM\Id
     * @ORM\Column(type="datekey")
     */
    private $Date;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $Open;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $High;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $Low;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $Close;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    private $Volume;

    public function getAsset(): ?Asset
    {
        return $this->Asset;
    }

    public function setAsset(Asset $asset): self
    {
        $this->Asset = $asset;

        return $this;
    }

    public function getDate(): ?DateKey
    {
        return $this->Date;
    }

    public function setDate(DateKey $Date): self
    {
        $this->Date = $Date;

        return $this;
    }

    public function getOpen(): ?string
    {
        return $this->Open;
    }

    public function getClose(): ?string
    {
        return $this->Close;
    }

    public function setOHLC(string $open, string $high, string $low, string $close): self
    {
        $this->Open = $open;
        $this->High = $high;
        $this->Low = $low;
        $this->Close = $close;

        return $this;
    }

    public function setVolume(string $volume): self
    {
        $this->Volume = $volume;

        return $this;
    }
}
