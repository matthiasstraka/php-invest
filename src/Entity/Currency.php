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

    /**
     * @ORM\Column(type="boolean")
     */
    private $Active;

    public function __construct($id, $code, $name, $active)
    {
        $this->id = $id;
        $this->Code = $code;
        $this->Name = $name;
        $this->Active = $active;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function getActive(): ?bool
    {
        return $this->Active;
    }
}
