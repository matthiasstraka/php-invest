<?php

namespace App\Entity;

use App\Repository\TransactionAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionAttachmentRepository::class)]
#[ORM\Table(name: "transaction_attachment")]
class TransactionAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Transaction::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private $transaction;

    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $mimetype;

    #[ORM\Column(type: "blob", length: 1048576, nullable: false)]
    private $content;

    #[ORM\Column(type: "datetime", nullable: false)]
    private $time_uploaded;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    public function setContent(string $filename, ?string $mimetype, $content)
    {
        $this->name = $filename;
        $this->mimetype = $mimetype;
        $this->content = $content;
        $this->time_uploaded = new \DateTime("now");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getTimeUploaded(): \DateTimeInterface
    {
        return $this->time_upload;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
}
