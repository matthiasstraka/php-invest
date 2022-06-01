<?php

namespace App\Entity;

use App\Entity\Instrument;
use Doctrine\ORM\Mapping as ORM;

// note: currently, we do not store the instrument price in the database but compute it on the fly (this may change in the future)
class InstrumentPrice
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private $instrument;

    #[ORM\Id]
    #[ORM\Column(type: "smallint", options: ["comment" => "Days since 1970-01-01"])]
    private $date;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $open;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $high;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $low;

    #[ORM\Column(type: "decimal", precision: 10, scale: 4)]
    private $close;

    private static $date_offset;

    public static function init()
    {
        self::$date_offset = new \DateTimeImmutable('1970-01-01');
    }

    public function getInstrument(): Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(Instrument $instrument): self
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return self::$date_offset->add(new \DateInterval("P{$this->date}D"));
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = self::getDateValue($date);

        return $this;
    }

    /*
     * Returns the integer value for the given date (days since 1970-01-01)
     */
    public static function getDateValue(\DateTimeInterface $date): int
    {
        return $date->diff(self::$date_offset)->days;
    }

    /*
     * Returns the date from an integer value (days since 1970-01-01)
     */
    public static function valueToDate(int $value): \DateTimeInterface
    {
        return self::$date_offset->add(new \DateInterval("P{$value}D"));
    }

    public function getOpen(): string
    {
        return $this->open;
    }

    public function getHigh(): string
    {
        return $this->high;
    }

    public function getLow(): string
    {
        return $this->low;
    }

    public function getClose(): string
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
}

InstrumentPrice::init();
