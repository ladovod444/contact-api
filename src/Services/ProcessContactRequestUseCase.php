<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;
use App\Services\Ai\ContactAiServiceInterface;
use App\Services\Mail\ContactEmailServiceInterface;
use App\Services\Statistics\ContactStatisticsServiceInterface;

/**
 * Обработка запроса /api/contacts
 */
class ProcessContactRequestUseCase implements ProcessContactRequestInterface
{
    public function __construct(
        private readonly ContactAiServiceInterface $aiService,
        private readonly ContactEmailServiceInterface $emailService,
        private readonly ContactStatisticsServiceInterface $statisticsService,
    ) {}

    public function execute(ContactDTO $dto, string $clientIp): ContactStatistics
    {

        // Проанализировать сообщение
        $analysisDto = $this->aiService->analyzeFeedback($dto->getComment());

        // Отправить email
        $this->emailService->send($analysisDto, $dto);

        // Создать и возвратить contactStatistics
        return $this->statisticsService->createStatistics($analysisDto, $dto, $clientIp);
    }
}
