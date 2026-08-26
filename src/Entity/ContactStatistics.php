<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactStatisticsRepository;
use App\ValueObject\EmailAddress;
use App\ValueObject\PhoneNumber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Статитстика отправки сообщений
 */
#[ORM\Entity(repositoryClass: ContactStatisticsRepository::class)]
class ContactStatistics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $ip;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'email_address', length: 255, nullable: true)]
    private ?EmailAddress $email = null;

    #[ORM\Column(type: 'phone_number', length: 255, nullable: true)]
    private ?PhoneNumber $phone = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sentiment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $autoReply = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?EmailAddress
    {
        return $this->email;
    }

    public function setEmail(?EmailAddress $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): self
    {
        $this->sentiment = $sentiment;
        return $this;
    }


    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getAutoReply(): ?string
    {
        return $this->autoReply;
    }

    public function setAutoReply(?string $autoReply): static
    {
        $this->autoReply = $autoReply;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

}
