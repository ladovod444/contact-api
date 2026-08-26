<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Message\SendEmailMessage;
use App\MessageHandler\SendEmailMessageHandler;
use App\Services\Mail\ContactEmailServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SendEmailMessageHandlerTest extends TestCase
{
    private ContactEmailServiceInterface&MockObject $emailService;
    private LoggerInterface&MockObject $logger;
    private SendEmailMessageHandler $handler;

    protected function setUp(): void
    {
        $this->emailService = $this->createMock(ContactEmailServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new SendEmailMessageHandler(
            $this->emailService,
            $this->logger
        );
    }

    public function testItCallsEmailServiceWithMessage(): void
    {
        $message = new SendEmailMessage(
            autoReply: 'Спасибо!',
            name: 'Иван',
            phone: '+79991234567',
            email: 'ivan@test.com',
            comment: 'Привет'
        );

        // Ожидание, что сервис будет вызван ровно один раз с нашим сообщением
        $this->emailService
            ->expects(self::once())
            ->method('send')
            ->with($message);

        // Ожидание, что будет залогирован успех
        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('Email успешно отправлен', self::anything());

        // Вызов хендлера
        ($this->handler)($message);
    }

    public function testItLogsErrorAndRethrowsExceptionOnFailure(): void
    {
        $message = new SendEmailMessage(
            autoReply: null,
            name: 'Иван',
            phone: '+79991234567',
            email: 'ivan@test.com',
            comment: 'Привет'
        );

        $exception = new \RuntimeException('SMTP timeout');

        $this->emailService
            ->method('send')
            ->willThrowException($exception);

        // Ожидание, что будет залогирована ошибка
        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Ошибка отправки email', self::anything());

        // Ожидание, что исключение будет переброшено (для Messenger retry)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('SMTP timeout');

        ($this->handler)($message);
    }
}
