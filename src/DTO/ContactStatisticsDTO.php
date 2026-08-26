<?php
declare(strict_types=1);

namespace App\DTO;

use App\Entity\ContactStatistics;
use App\ValueObject\EmailAddress;
use App\ValueObject\PhoneNumber;

class ContactStatisticsDTO
{
    private ?string $name = null;

    private ?PhoneNumber $phone = null;

    private ?EmailAddress $email = null;

    private ?string $comment = null;

    private ?string $sentiment = null;

    private ?string $category = null;

    private ?string $autoReply = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getEmail(): ?EmailAddress
    {
        return $this->email;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getAutoReply(): ?string
    {
        return $this->autoReply;
    }

    public static function fromEntity(ContactStatistics $contactStatistics): self
    {
        $instance = new self();
        $instance->name = $contactStatistics->getName();
        $instance->phone = $contactStatistics->getPhone();
        $instance->email = $contactStatistics->getEmail();
        $instance->comment = $contactStatistics->getComment();
        $instance->sentiment = $contactStatistics->getSentiment();
        $instance->category = $contactStatistics->getCategory();
        $instance->autoReply = $contactStatistics->getAutoReply();

        return $instance;
    }
}
