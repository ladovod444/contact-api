<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\DTO\AiAnalysisDTO;
use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;

interface ContactStatisticsServiceInterface
{
    public function createStatistics(AiAnalysisDTO $analysisDTO, ContactDTO $contactDTO, string $ip): ContactStatistics;
}
