<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Проверка статуса сервиса
 */
#[WithMonologChannel('health')]
class HealthController extends AbstractController
{
    function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
    ) {}

    #[Route('/api/health', methods: ['GET'])]
    #[OA\Get(
        path: '/api/health',
        summary: 'Статус системы',
        description: 'Получение статуса системы.',
        tags: ['Статус']
    )]
    #[OA\Response(
        response: 200,
        description: 'Система работает (статус healthy или degraded)',
        content: new OA\JsonContent(
            type: 'object',
            required: ['status', 'timestamp', 'components', 'version'],
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    enum: ['healthy', 'degraded'],
                    example: 'healthy',
                    description: 'Общий статус системы'
                ),
                new OA\Property(
                    property: 'timestamp',
                    type: 'string',
                    format: 'date-time',
                    example: '2023-10-27T12:00:00+00:00',
                    description: 'Время проверки в формате ISO 8601 (ATOM)'
                ),
                new OA\Property(
                    property: 'version',
                    type: 'string',
                    example: 'v1',
                    description: 'Текущая версия API из параметров'
                ),
                new OA\Property(
                    property: 'components',
                    type: 'object',
                    description: 'Статус отдельных компонентов системы',
                    properties: [
                        new OA\Property(
                            property: 'database',
                            type: 'string',
                            enum: ['ok', 'degraded'],
                            example: 'ok',
                            description: 'Статус подключения к базе данных'
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера (если упадет сам механизм логирования или параметр)',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal Server Error')
            ]
        )
    )]
    public function health(): JsonResponse
    {
        $components = [
            'database' => 'degraded', // по умолчанию
        ];

        // Проверка БД
        try
        {
            $this->entityManager->getConnection()->getDatabase();
            $components['database'] = 'ok';
        }
        catch(\Exception $e)
        {
            // Логировать ошибку, но не показывать детали в ответе
            $this->logger->error("Ошибка при подлючении к БД", [
                'error' => $e->getMessage()
            ]);
        }

        $overallStatus = in_array('degraded', $components) ? 'degraded' : 'healthy';

        // Логировать статус
        $this->logger->info("Текущий статус: {$overallStatus}");

        $version = $this->parameterBag->get('api_version');

        $timestamp = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);

        $responseData = [
            'status' => $overallStatus,
            'timestamp' => $timestamp,
            'components' => $components,
            'version' => $version,
        ];

        // Возвращаем 503, если система деградировала, иначе 200
        $httpCode = $overallStatus === 'degraded'
            ? JsonResponse::HTTP_SERVICE_UNAVAILABLE
            : JsonResponse::HTTP_OK;

        return new JsonResponse($responseData, $httpCode);
    }
}
