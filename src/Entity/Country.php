<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @Constraints\Range(min=0,max=999)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Constraints\NotBlank
     */
    private $Name;

    public function __construct($code, $name)
    {
        $this->id = $code;
        $this->Name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $name): self
    {
        $this->Name = $name;
        return $this;
    }
}
