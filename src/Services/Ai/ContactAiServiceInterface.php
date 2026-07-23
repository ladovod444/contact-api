<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\AiAnalysisDTO;

interface ContactAiServiceInterface
{
    public function analyzeFeedback(string $comment): AiAnalysisDTO;
}
