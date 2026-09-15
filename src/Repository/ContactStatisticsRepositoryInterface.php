<?php

declare(strict_types=1);

namespace App\Repository;

interface ContactStatisticsRepositoryInterface
{
    public function getMetrics(?\DateTimeImmutable $dateFrom, ?\DateTimeImmutable $dateTo): array;
}
