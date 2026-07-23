<?php

declare(strict_types=1);

namespace App\Repository;

use DateTimeImmutable;

interface ContactStatisticsRepositoryInterface
{
    public function getMetrics(?DateTimeImmutable $dateFrom, ?DateTimeImmutable $dateTo): array;
}
