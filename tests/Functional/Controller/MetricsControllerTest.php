<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\ContactStatisticsRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class MetricsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testItReturnsMetricsForAllTimeWhenNoDatesProvided(): void
    {
        // Мокаем репозиторий, чтобы тест не зависел от реальной БД
        $repoMock = $this->createMock(ContactStatisticsRepositoryInterface::class);
        $repoMock->expects(self::once())
            ->method('getMetrics')
            ->with(null, null) // Ожидаем, что методы вызовутся с null
            ->willReturn([['sentiment' => 'positive', 'total' => 10]]);

        static::getContainer()->set(ContactStatisticsRepositoryInterface::class, $repoMock);

        $this->client->request('GET', '/api/metrics');

        self::assertResponseIsSuccessful();
        self::assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('metrics', $data);
    }

    public function testItReturnsMetricsForValidDateRange(): void
    {
        $repoMock = $this->createMock(ContactStatisticsRepositoryInterface::class);
        $repoMock->expects(self::once())
            ->method('getMetrics')
            // Проверяем, что строки корректно преобразовались в DateTimeImmutable
            ->with(
                self::callback(fn($d) => $d instanceof \DateTimeImmutable && $d->format('Y-m-d') === '2023-10-01'),
                self::callback(fn($d) => $d instanceof \DateTimeImmutable && $d->format('Y-m-d') === '2023-10-31')
            )
            ->willReturn([]);

        static::getContainer()->set(ContactStatisticsRepositoryInterface::class, $repoMock);

        $this->client->request('GET', '/api/metrics?dateFrom=2023-10-01&dateTo=2023-10-31');

        self::assertResponseIsSuccessful();
    }

    public function testItReturns422OnInvalidDateFormat(): void
    {

        $this->client->request(
            'GET',
            '/api/metrics?dateFrom=invalid-date', server: [
            'HTTP_ACCEPT' => 'application/json'
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY); // Ожидаем 422

        $data = json_decode($this->client->getResponse()->getContent(), true);

        self::assertArrayHasKey('violations', $data);
    }
}
