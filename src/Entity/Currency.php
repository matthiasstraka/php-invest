<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[UniqueEntity("Code", message: "Currency already in list")]
class Currency
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 3, options: ["fixed" => true, "comment" => "ISO 4217 Code"])]
    #[Assert\Currency]
    private $Code;

    // ISIN that maps tracks the currency conversion to USD
    #[ORM\Column(type: Types::STRING, length: 12, nullable: true, options: ["fixed" => true])]
    #[Assert\Isin]
    private $isin_usd;

    public function __construct(string $code, ?string $isin = null)
    {
        $this->Code = $code;
        $this->isin_usd = $isin;
    }

    public function getCode(): string
    {
        return $this->Code;
    }

    public function setCode(string $code): self
    {
        $this->Code = $code;
        return $this;
    }

    public function getIsinUsd(): ?string
    {
        return $this->isin_usd;
    }

    public function setIsinUsd(?string $isin): self
    {
        $this->isin_usd = $isin;
        return $this;
    }

    public function __toString(): string
    {
        return $this->Code;
    }
}
