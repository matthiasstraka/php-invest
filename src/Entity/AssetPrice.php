<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Repository\AssetPriceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetPriceRepository::class)]
class AssetPrice
{
    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["comment" => "Days since 1970-01-01"])]
    private $date;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Asset::class)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private $asset;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $open;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $high;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $low;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $close;

    #[ORM\Column(type: "integer", options: ["unsigned" => true])]
    private $volume = 0;

    private static $date_offset;

    public static function init()
    {
        self::$date_offset = new \DateTimeImmutable('1970-01-01');
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return self::$date_offset->add(new \DateInterval("P{$this->date}D"));
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date->diff(self::$date_offset)->days;

        return $this;
    }

    public function getOpen(): ?string
    {
        return $this->open;
    }

    public function getHigh(): ?string
    {
        return $this->high;
    }

    public function getLow(): ?string
    {
        return $this->low;
    }

    public function getClose(): ?string
    {
        return $this->close;
    }

    public function setOHLC(string $open, string $high, string $low, string $close): self
    {
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;

        return $this;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function setOpen(string $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function setHigh(string $high): self
    {
        $this->high = $high;

        return $this;
    }

    public function setLow(string $low): self
    {
        $this->low = $low;

        return $this;
    }

    public function setClose(string $close): self
    {
        $this->close = $close;

        return $this;
    }

    public function setVolume(int $volume): self
    {
        $this->volume = $volume;

        return $this;
    }
}

AssetPrice::init();
