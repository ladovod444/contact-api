<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;

interface ProcessContactRequestInterface
{
    public function execute(ContactDTO $dto, string $clientIp): ContactStatistics;
}
