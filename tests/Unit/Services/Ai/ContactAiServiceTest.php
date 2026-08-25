<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Ai;

use App\Services\Ai\ContactAiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ContactAiServiceTest extends TestCase
{
    public function testItParsesValidProviderResponse(): void
    {
        $payload = json_encode([
            'choices' => [[
                'message' => [
                    'content' => '{"sentiment":"negative","category":"bug","autoReply":"Извините"}',
                ],
            ]],
        ]);

        $service = $this->makeService(new MockResponse($payload));

        $result = $service->analyzeFeedback('Всё сломано');

        self::assertSame('negative', $result->getSentiment());
        self::assertSame('bug', $result->getCategory());
        self::assertSame('Извините', $result->getAutoReply());
    }

    public function testItReturnsNullOnMalformedJson(): void
    {
        $service = $this->makeService(new MockResponse('не json вовсе'));

        self::assertNull($service->analyzeFeedback('Текст'));
    }

    public function testItReturnsNullOnProviderError(): void
    {
        $service = $this->makeService(new MockResponse('', ['http_code' => 503]));

        self::assertNull($service->analyzeFeedback('Текст'));
    }

    public function testItReturnsNullOnUnexpectedSentimentValue(): void
    {
        $payload = json_encode([
            'choices' => [[
                'message' => ['content' => '{"sentiment":"очень хорошо","category":"bug"}'],
            ]],
        ]);

        $service = $this->makeService(new MockResponse($payload));

        self::assertNull($service->analyzeFeedback('Текст'));
    }

    private function makeService(MockResponse $response): ContactAiService
    {
        return new ContactAiService(
            new MockHttpClient($response),
            'test_key',
            'https://ai.example.test/v1/chat',
            new NullLogger(),
        );
    }
}
