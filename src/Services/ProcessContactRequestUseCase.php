<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;
use App\Services\Ai\ContactAiServiceInterface;
use App\Services\Mail\ContactEmailServiceInterface;
use App\Services\Statistics\ContactStatisticsServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Обработка запроса /api/contacts
 */
class ProcessContactRequestUseCase implements ProcessContactRequestInterface
{
    public function __construct(
        private readonly ContactAiServiceInterface $aiService,
        private readonly ContactEmailServiceInterface $emailService,
        private readonly ContactStatisticsServiceInterface $statisticsService,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(ContactDTO $dto, string $clientIp): ContactStatistics
    {

        // Проанализировать сообщение
        $analysisDto = null;

        try
        {
            // 2. Пытаемся получить результат обработки комментария
            $analysisDto = $this->aiService->analyzeFeedback($dto->getComment());
        }
        catch(\RuntimeException $e)
        {
            // 3. Ловим ошибку
            $this->logger->error('AI analysis failed: '.$e->getMessage(), ['comment' => $dto->getComment()]);

        }

        // Отправить email
        $this->emailService->send($analysisDto, $dto);

        // Создать и возвратить contactStatistics
        return $this->statisticsService->createStatistics($analysisDto, $dto, $clientIp);
    }
}
