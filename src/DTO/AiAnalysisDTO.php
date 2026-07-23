<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AiAnalysisDTO
{
    function __construct(
        #[Assert\NotBlank]
        private string $sentiment,

        #[Assert\NotBlank]
        private string $category,

        #[Assert\NotBlank]
        private ?string $autoReply,
    ) {}

    public function getSentiment(): string
    {
        return $this->sentiment;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getAutoReply(): ?string
    {
        return $this->autoReply;
    }
}
