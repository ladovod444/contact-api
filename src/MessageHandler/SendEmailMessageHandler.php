<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Services\Mail\ContactEmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private readonly ContactEmailServiceInterface $contactEmailService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        try {
            $this->contactEmailService->send($message);

            $this->logger->info('Email успешно отправлен', [
                'to' => $message->getEmail(),
                'name' => $message->getName(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Ошибка отправки email', [
                'to' => $message->getEmail(),
                'error' => $e->getMessage(),
            ]);

            // Перебрросить исключение, чтобы Messenger сделал retry
            throw $e;
        }
    }
}
