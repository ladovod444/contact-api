<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ContactStatisticsRepositoryInterface;
use Monolog\Attribute\WithMonologChannel;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[WithMonologChannel('metrics')]
class MetricsController extends AbstractController
{
    function __construct(
        private ContactStatisticsRepositoryInterface $statisticsRepository,
        private LoggerInterface $logger
    ) {}

    #[Route('/api/metrics', methods: ['GET'])]
    #[OA\Get(
        path: '/api/metrics',
        summary: 'Получение статистики и метрик',
        description: 'Возвращает агрегированные метрики. Можно отфильтровать по периоду с помощью query-параметров.',
        tags: ['Статистика']
    )]
    #[OA\Parameter(
        name: 'dateFrom',
        in: 'query',
        description: 'Начальная дата периода (формат YYYY-MM-DD)',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2023-10-01')
    )]
    #[OA\Parameter(
        name: 'dateTo',
        in: 'query',
        description: 'Конечная дата периода (формат YYYY-MM-DD)',
        required: false,
        schema: new OA\Schema(type: 'string', format: 'date', example: '2023-10-31')
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешный ответ с данными метрик',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'metrics',
                    description: 'Объект или массив с данными метрик',
                    type: 'object', // Измените на 'array', если getMetrics возвращает список
                    example: [
                        'total_requests' => 1500,
                        'unique_users' => 340,
                        'avg_response_time' => 0.12
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Некорректный формат даты',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Invalid date format')
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {

        $dateFrom = $request->query->get('dateFrom') ? new \DateTimeImmutable($request->query->get('dateFrom')) : null;
        $dateTo = $request->query->get('dateTo') ? new \DateTimeImmutable($request->query->get('dateTo')) : null;

        // Логировать статистику
        if($dateFrom && $dateTo)
        {
            $this->logger->info('Показана статистика за период', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
        }
        else
        {
            $this->logger->info('Показана статистика за весь период');
        }
        $metrics = $this->statisticsRepository->getMetrics($dateFrom, $dateTo);

        return $this->json(['metrics' => $metrics]);
    }
}
