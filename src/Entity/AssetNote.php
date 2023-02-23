<?php

namespace App\Entity;

use App\Entity\Asset;
use App\Entity\User;
use App\Repository\AssetNoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssetNoteRepository::class)]
class AssetNote
{
    const TYPE_NOTE = 0;
    const TYPE_NEWS = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Asset::class)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private $asset;

    #[ORM\Column(type: "string", length: 255)]
    private $title;

    #[ORM\Column(type: "date")]
    private $date;

    #[ORM\Column(type: "smallint", options: ["unsigned" => true])]
    #[Assert\Choice(choices: [
        self::TYPE_NOTE,
        self::TYPE_NEWS,
      ])]
    private $type = self::TYPE_NOTE;

    #[ORM\Column(type: "text", length: 65535)]
    private $text;

    #[ORM\Column(type: "string", length: 2048, nullable: true)]
    #[Assert\Url]
    private $url;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $author;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $user): self
    {
        $this->author = $user;

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
            case self::TYPE_NOTE:
                return "Note";
            case self::TYPE_NEWS:
                return "News";
            default:
                return "Unknown";
        }
    }

    public function getTypeName(): string
    {
        return self::typeNameFromValue($this->type);
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
