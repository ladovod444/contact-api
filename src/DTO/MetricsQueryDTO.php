<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MetricsQueryDTO
{
    public function __construct(
        #[Assert\Date(message: 'Некорректный формат даты dateFrom (ожидается YYYY-MM-DD)')]
        public ?string $dateFrom = null,

        #[Assert\Date(message: 'Некорректный формат даты dateTo (ожидается YYYY-MM-DD)')]
        public ?string $dateTo = null,
    ) {}

    public function getDateFromImmutable(): ?\DateTimeImmutable
    {
        return $this->dateFrom ? new \DateTimeImmutable($this->dateFrom) : null;
    }

    public function getDateToImmutable(): ?\DateTimeImmutable
    {
        return $this->dateTo ? new \DateTimeImmutable($this->dateTo) : null;
    }
}
