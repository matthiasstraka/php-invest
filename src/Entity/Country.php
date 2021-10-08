<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $Code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    public function __construct($code, $name)
    {
        $this->Code = $code;
        $this->Name = $name;
    }

    public function getCode(): ?int
    {
        return $this->Code;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }
}
