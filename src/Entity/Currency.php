<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 * @UniqueEntity("Code", message="Currency already in list")
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=3, options={"fixed": true, "comment": "ISO 4217 Code"})
     * @Assert\Currency
     */
    private $Code;

    public function __construct($code)
    {
        $this->Code = $code;
    }

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function setCode(string $code): self
    {
        $this->Code = $code;
        return $this;
    }

    public function __toString(): string
    {
        return $this->Code;
    }
}
