<?php

namespace App\Entity;

use App\Repository\AssetTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity(repositoryClass=AssetTypeRepository::class)
 */
class AssetType {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="smallint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Constraints\NotBlank
     */
    private $Name;

    public function __construct($name)
    {
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

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $name):self
    {
        $this->Name = $name;
        return $this;
    }

    public function __toString()
    {
        return $this->Name;
    }
}
