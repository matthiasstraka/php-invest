<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Type\DateKey;
use App\Repository\AssetPriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass=AssetPriceRepository::class)
 * @ORM\Table(
 *   uniqueConstraints={
 *     @UniqueConstraint(name="UNIQ_asset_date", 
 *            columns={"asset_id", "date"})
 *    }
 * )
 */
class AssetPrice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $Id;

    /**
     * @ORM\ManyToOne(targetEntity="Asset", cascade={"all"})
     */
    private $Asset;

    /**
     * @ORM\Column(type="date")
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
     * @ORM\Column(type="integer", options={"unsigned"=true})
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

    public function getDate(): ?\DateTime
    {
        return $this->Date;
    }

    public function setDate(\DateTime $Date): self
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

    public function setVolume(int $volume): self
    {
        $this->Volume = $volume;

        return $this;
    }
}
