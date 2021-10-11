<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @Constraints\Range(min=0,max=999)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, unique=true, options={"fixed":true})
     * @Constraints\Length(min=3,max=3)
     */
    private $Code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Constraints\NotBlank
     */
    private $Name;

    public function __construct($id, $code, $name)
    {
        $this->id = $id;
        $this->Code = $code;
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

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function setCode(string $code): self
    {
        $this->Code = $code;
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
