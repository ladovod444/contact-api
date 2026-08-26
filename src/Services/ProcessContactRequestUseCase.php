<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;
use App\Message\SendEmailMessage;
use App\Services\Ai\ContactAiServiceInterface;
use App\Services\Statistics\ContactStatisticsServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Обработка запроса /api/contacts
 */
class ProcessContactRequestUseCase implements ProcessContactRequestInterface
{
    public function __construct(
        private readonly ContactAiServiceInterface $aiService,
        private readonly ContactStatisticsServiceInterface $statisticsService,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function execute(ContactDTO $dto, string $clientIp): ContactStatistics
    {

        // Проанализировать сообщение
        $analysisDto = null;

        try
        {
            // Попытка получить результат обработки комментария
            $analysisDto = $this->aiService->analyzeFeedback($dto->getComment());
        }
        catch(\RuntimeException $e)
        {
            // Словить ошибку
            $this->logger->error('AI analysis failed: '.$e->getMessage(), ['comment' => $dto->getComment()]);
        }

        // Создать contactStatistics
        $contactStatistics = $this->statisticsService->createStatistics($analysisDto, $dto, $clientIp);

        // Отправить email асинхронно
        $sendEmailMessage = new SendEmailMessage(
            autoReply: $analysisDto?->getAutoReply(),
            name: $dto->getName(),
            phone: $dto->getPhone(),
            email: $dto->getEmail(),
            comment: $dto->getComment()
        );

        $this->messageBus->dispatch($sendEmailMessage);

        return $contactStatistics;
    }
}
