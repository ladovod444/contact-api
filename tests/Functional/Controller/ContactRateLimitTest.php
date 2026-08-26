<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DTO\AiAnalysisDTO;
use App\Services\Ai\ContactAiServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ContactRateLimitTest extends WebTestCase
{
    public function testItReturns429AfterLimitExceeded(): void
    {
        $client = static::createClient();

//        $client->catchExceptions(false);

        $ai = $this->createMock(ContactAiServiceInterface::class);
        $ai->method('analyzeFeedback')->willReturn(new AiAnalysisDTO('neutral', 'support', 'ok'));
        static::getContainer()->set(ContactAiServiceInterface::class, $ai);

        $limit = 10; // подставьте реальный лимит из конфига
        $statuses = [];

        for ($i = 0; $i <= $limit; ++$i) {
            $client->request(
                'POST',
                '/api/contact',
                server: ['CONTENT_TYPE' => 'application/json'],
                content: json_encode([
                    'name' => 'Иван',
                    'phone' => '+79991234567',
                    'email' => "ivan{$i}@example.com",
                    'comment' => 'Повторяющееся обращение для проверки лимита',
                ]),
            );

            $statuses[] = $client->getResponse()->getStatusCode();

        }

        self::assertContains(Response::HTTP_TOO_MANY_REQUESTS, $statuses, 'Rate limiter не сработал');
    }
}
