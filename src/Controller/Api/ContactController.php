<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\ContactDTO;
use App\Services\ProcessContactRequestInterface;
use Monolog\Attribute\WithMonologChannel;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[WithMonologChannel('contact')]
class ContactController extends AbstractController
{
    function __construct(
        private ProcessContactRequestInterface $contactRequest,
        private LoggerInterface $logger,
        #[Target('contact_api')] private RateLimiterFactoryInterface $rateLimiter,
    ) {}

    #[Route('/api/contact', methods: ['POST'])]
    #[OA\Post(
        path: '/api/contact',
        summary: 'Отправить контактную форму',
        description: 'Эндпоинт для отправки данных контактной формы',
        tags: ['Контакты'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'phone', 'email', 'comment'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов'),
                    new OA\Property(property: 'phone', type: 'string', example: '+79991234567'),
                    new OA\Property(property: 'email', type: 'string', example: 'ivan@example.com'),
                    new OA\Property(property: 'comment', type: 'string', example: 'Хочу узнать больше о вашем продукте'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Сообщение успешно отправлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Спасибо за обращение!'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function index(
        #[MapRequestPayload] ContactDTO $dto,
        Request $request,
    ): JsonResponse
    {

        // Задать Rate limiter
        $clientIp = $request->getClientIp();
        $limiter = $this->rateLimiter->create($clientIp);

        if(false === $limiter->consume(1)->isAccepted())
        {
            $this->logger->warning("Заблокирован запрос:", [
                'ip' => $clientIp,
                'name' => $dto->getName(),
                'email' => $dto->getEmail(),
                'comment' => $dto->getComment(),
            ]);
            return $this->json(['error' => 'Too many requests'], 429);
        }

        // Отправить полученные данные на обработку
        $contactStatistics = $this->contactRequest->execute($dto, $clientIp);

        // Логировать результат
        $this->logger->info("Создана сущность с данными:", [
            'name' => $contactStatistics->getName(),
            'email' => $contactStatistics->getEmail(),
            'comment' => $contactStatistics->getComment(),
            'category' => $contactStatistics->getCategory(),
            'ip' => $clientIp,
            'sentiment' => $contactStatistics->getSentiment(),
            'autoReply' => $contactStatistics->getAutoReply(),
        ]);

        return $this->json($contactStatistics, Response::HTTP_CREATED);
    }

}
