<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    const TYPE_CASH = 1;
    const TYPE_MARGIN = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string")]
    private $name;

    #[ORM\Column(type: "string", nullable: true)]
    private $number;

    #[ORM\Column(type: "smallint", options: ["default" => self::TYPE_CASH])]
    private $type = self::TYPE_CASH;

    #[ORM\Column(type: "string", length: 3, options: ["fixed" => true, "comment" => "ISO 4217 Code"])]
    #[Assert\NotBlank]
    #[Assert\Currency]
    private $currency;

    #[ORM\Column(type: "string")]
    #[Assert\Timezone]
    private $timezone;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\Column(type: "boolean", options: ["default" => false, "comment" => "User's favorite"])]
    private $star = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public static function typeNameFromValue(int $type): string
    {
        switch ($type) {
            case self::TYPE_CASH:
                return "Cash";
            case self::TYPE_MARGIN:
                return "Margin";
            default:
                return "Unknown";
        }
    }

    public function getTypeName(): string
    {
        return self::typeNameFromValue($this->type);
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function hasStar(): bool
    {
        return $this->star;
    }

    public function setStar(bool $star): self
    {
        $this->star = $star;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function __toString(): string 
    {
        return $this->name;
    }
}
