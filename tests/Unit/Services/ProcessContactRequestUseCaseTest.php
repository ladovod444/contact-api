<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\DTO\AiAnalysisDTO;
use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;
use App\Message\SendEmailMessage;
use App\Services\Ai\ContactAiServiceInterface;
use App\Services\ProcessContactRequestUseCase;
use App\Services\Statistics\ContactStatisticsServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProcessContactRequestUseCaseTest extends TestCase
{
    private ContactStatisticsServiceInterface&MockObject $statistics;
    private ContactAiServiceInterface&MockObject $ai;

    private MessageBusInterface&MockObject $messageBus;
    private ProcessContactRequestUseCase $useCase;

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->statistics = $this->createMock(ContactStatisticsServiceInterface::class);
        $this->ai = $this->createMock(ContactAiServiceInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->useCase = new ProcessContactRequestUseCase(
            $this->ai,
            $this->statistics,
            $this->logger,
            $this->messageBus,
        );
    }

    public function testItSavesRequestEnrichesWithAiAndSendsEmails(): void
    {
        $contactDto = $this->makeContactDto('Хочу оставить отзыв, всё понравилось');
        $entity = new ContactStatistics()
            ->setCategory('support')
            ->setSentiment('positive')
            ->setAutoReply('Спасибо за отзыв!');

        $analysisDTO = new AiAnalysisDTO('positive', 'support', 'Спасибо за отзыв!');

        $this->ai
            ->expects(self::once())
            ->method('analyzeFeedback')
            ->with($contactDto->getComment())
            ->willReturn($analysisDTO);

        $this->statistics
            ->expects(self::once())
            ->method('createStatistics')
            ->with($analysisDTO, $contactDto, '203.0.113.10')
            ->willReturn($entity);


        $sendEmailMessage = $this->makeSendEmailMessage($entity, $contactDto);

        $envelope = new Envelope($sendEmailMessage, []);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with($sendEmailMessage)
            ->willReturn($envelope);

        $result = $this->useCase->execute($contactDto, '203.0.113.10');

        self::assertSame('positive', $result->getSentiment());
        self::assertSame('support', $result->getCategory());
        self::assertSame('Спасибо за отзыв!', $result->getAutoReply());
    }

    /**
     * Ключевой тест: AI недоступен — сервис обязан продолжить работу.
     */
    public function testItStillSavesAndSendsEmailsWhenAiFails(): void
    {
        $contactDto = $this->makeContactDto('Ничего не работает!');
        $entity = new ContactStatistics()
            ->setCategory('support');

        $this->ai
            ->expects(self::once())
            ->method('analyzeFeedback')
            ->willThrowException(new \RuntimeException('AI provider timeout'));

        $this->statistics
            ->method('createStatistics')
            ->willReturn($entity);

        $sendEmailMessage = $this->makeSendEmailMessage($entity, $contactDto);

        $envelope = new Envelope($sendEmailMessage, []);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with($sendEmailMessage)
            ->willReturn($envelope);


        $result = $this->useCase->execute($contactDto, '203.0.113.10');

        self::assertNull($result->getSentiment());
        self::assertNull($result->getAutoReply());
    }

    public function testItPersistsRequestBeforeCallingAi(): void
    {
        $contactDto = $this->makeContactDto('Проверка порядка вызовов');
        $entity = new ContactStatistics();
        $calls = [];


        $this->ai
            ->method('analyzeFeedback')
            ->willReturnCallback(function() use (&$calls) {
                $calls[] = 'ai';

                return new AiAnalysisDTO('neutral', 'support', 'ok');
            });

        $this->statistics
            ->method('createStatistics')
            ->willReturnCallback(function() use (&$calls, $entity) {
                $calls[] = 'persist';

                return $entity;
            });

        $sendEmailMessage = $this->makeSendEmailMessage($entity, $contactDto);

        $envelope = new Envelope($sendEmailMessage, []);
        $this->messageBus
            ->method('dispatch')
            ->willReturnCallback(function() use (&$calls, $envelope) {
                $calls[] = 'email';

                return $envelope;
            });


        $this->useCase->execute($contactDto, '203.0.113.10');

        // Проверить что в ProcessContactRequestUseCase методы сервисов сработали в нужной последовательности
        self::assertSame(['ai',  'persist', 'email'], $calls);
    }

    private function makeContactDto(string $comment): ContactDTO
    {
        return new ContactDTO(
            name: 'Иван Петров',
            phone: '+7 (999) 123-45-67',
            email: 'ivan@example.com',
            comment: $comment,
        );
    }

    private function makeSendEmailMessage(ContactStatistics $entity, ContactDTO $contactDto): SendEmailMessage
    {
        return new SendEmailMessage(
            autoReply: $entity->getAutoReply(),
            name: $contactDto->getName(),
            phone: $contactDto->getPhone(),
            email: $contactDto->getEmail(),
            comment: $contactDto->getComment()
        );
    }
}
