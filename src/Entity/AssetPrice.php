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
     * @ORM\ManyToOne(targetEntity="Asset")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $Asset;

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
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
    private $Volume = 0;

    public function getAsset(): ?Asset
    {
        return $this->Asset;
    }

    public function setAsset(Asset $asset): self
    {
        $this->Asset = $asset;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        $date_offset = new \DateTimeImmutable('1980-01-01');
        return $date_offset->add(new \DateInterval("P{$this->Date}D"));
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $date_offset = new \DateTimeImmutable('1980-01-01');
        $this->Date = $Date->diff($date_offset)->days;

        return $this;
    }

    public function getOpen(): ?string
    {
        return $this->Open;
    }

    public function getHigh(): ?string
    {
        return $this->High;
    }

    public function getLow(): ?string
    {
        return $this->Low;
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

    public function getVolume(): int
    {
        return $this->Volume;
    }

    public function setVolume(int $volume): self
    {
        $this->Volume = $volume;

        return $this;
    }
}
