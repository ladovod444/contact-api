<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SendEmailMessage
{
    public function __construct(
        private ?string $autoReply,
        private string $name,
        private string $phone,
        private string $email,
        private string $comment
    ) {
    }

    public function getAutoReply(): ?string
    {
        return $this->autoReply;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
