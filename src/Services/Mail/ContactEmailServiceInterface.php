<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\DTO\AiAnalysisDTO;
use App\DTO\ContactDTO;

interface ContactEmailServiceInterface
{
    public function send(AiAnalysisDTO $analysisDTO, ContactDTO $contactDTO): void;
}
