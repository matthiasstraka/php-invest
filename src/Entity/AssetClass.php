<?php

namespace App\Entity;

use App\Repository\AssetClassRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity(repositoryClass=AssetClassRepository::class)
 */
class AssetClass {
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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName($name)
    {
        $this->Name = $name;
    }
}
