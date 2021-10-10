<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true})
     */
    private $Code;

    /**
     * @ORM\Column(type="string", length=255)
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

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function setCode(string $code)
    {
        $this->Code = $code;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $name)
    {
        $this->Name = $name;
    }
}
