<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DTO\AiAnalysisDTO;
use App\Repository\ContactStatisticsRepository;
use App\Repository\ContactStatisticsRepositoryInterface;
use App\Services\Ai\ContactAiServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testItAcceptsValidRequestAndPersistsIt(): void
    {
        $this->stubAi(new AiAnalysisDTO('positive', 'support', 'Спасибо!'));

        $this->post([
            'name' => 'Иван Петров',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivan@example.com',
            'comment' => 'Всё понравилось, спасибо за работу',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('sentiment', $data);
        self::assertSame('positive', $data['sentiment']);

        // внутренние данные наружу не утекают
        self::assertArrayNotHasKey('ip', $data);

        $repository = static::getContainer()->get(ContactStatisticsRepositoryInterface::class);
        self::assertCount(1, $repository->findAll());

        self::assertEmailCount(1);
    }

    public function testItReturnsValidationErrorOnInvalidEmail(): void
    {
        $this->post([
            'name' => 'Иван',
            'phone' => '+79991234567',
            'email' => 'не-email',
            'comment' => 'Текст обращения',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertEmailCount(0);
    }

    public function testItReturnsValidationErrorOnMissingFields(): void
    {
        $this->post(['name' => 'Иван']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testItRejectsMalformedJson(): void
    {
        $this->client->request(
            'POST',
            '/api/contact',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{сломанный json',
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testItStillAcceptsRequestWhenAiIsDown(): void
    {
        $ai = $this->createMock(ContactAiServiceInterface::class);
        $ai->method('analyzeFeedback')->willThrowException(new \RuntimeException('AI down'));
        static::getContainer()->set(ContactAiServiceInterface::class, $ai);

        $this->post([
            'name' => 'Иван Петров',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'comment' => 'Обращение при недоступном AI',
        ]);

        self::assertResponseIsSuccessful();

        $repository = static::getContainer()->get(ContactStatisticsRepository::class);
        self::assertCount(1, $repository->findAll());
        self::assertEmailCount(1);
    }

    public function testItReturnsErrorResponseAsJsonNotHtml(): void
    {
        $this->post([
            'name' => '',
            'phone' => '',
            'email' => 'bad',
            'comment' => '',
        ]);

        $content = $this->client->getResponse()->getContent();

        self::assertJson($content);
        self::assertStringNotContainsString('<html', $content);
    }

    private function post(array $payload): void
    {
        $this->client->request(
            'POST',
            '/api/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    private function stubAi(AiAnalysisDTO $result): void
    {
        $ai = $this->createMock(ContactAiServiceInterface::class);
        $ai->method('analyzeFeedback')->willReturn($result);
        static::getContainer()->set(ContactAiServiceInterface::class, $ai);
    }
}
