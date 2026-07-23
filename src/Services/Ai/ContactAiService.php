<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTO\AiAnalysisDTO;
use InvalidArgumentException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Анализирует данные по отпраленному сообщению
 * Отдает данные AiAnalysisDTO
 */
#[WithMonologChannel('contact')]
class ContactAiService implements ContactAiServiceInterface
{

    private const string AI_MODEL = 'claude-opus-4.8';
    private const string AI_PROMPT = 'Ты аналитик обратной связи. Верни ответ строго в виде JSON без какой‑либо разметки, без ```json, без лишних слов. Только JSON-объект строго по схеме: {"sentiment": "positive|negative|neutral", "category": "billing|support|bug|feature", "autoReply": "текст"}';
    function __construct(
        private readonly HttpClientInterface $httpClient,
        private string $apiKey,
        private string $apiUrl,
        private LoggerInterface $logger,
    ) {}

    public function analyzeFeedback(string $comment): AiAnalysisDTO
    {

        // Если в .env не указаны параметры:

        if(null === $this->apiKey)
        {
            throw new InvalidArgumentException('Не указан ai_api_key');
        }

        if(null === $this->apiUrl)
        {
            throw new InvalidArgumentException('Не указан ai_api_url');
        }

        try
        {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->apiKey ? 'Bearer '.$this->apiKey : null,
                ],
                'json' => [
                    'model' => self::AI_MODEL,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => self::AI_PROMPT,
                        ],
                        ['role' => 'user', 'content' => $comment],
                    ],
                ],
            ]);

            $data = $response->toArray();

            $content = json_decode($data['choices'][0]['message']['content'], true, 512, JSON_THROW_ON_ERROR);

            if(!$content)
            {
                $this->logger->error("Ошибка при обращении к  AI");
                return new AiAnalysisDTO('neutral', 'other', null);
            }

            $aiAnalysisDTO = new AiAnalysisDTO(
                sentiment: $content['sentiment'] ?? 'neutral',
                category: $content['category'] ?? 'other',
                autoReply: $content['autoReply'] ?? null,
            );

            $this->logger->info("Получены данные от AI: ", [
                'sentiment' => $aiAnalysisDTO->getSentiment(),
                'category' => $aiAnalysisDTO->getCategory(),
                'autoReply' => $aiAnalysisDTO->getAutoReply(),
            ]);

            return $aiAnalysisDTO;
        }
        catch(\Throwable $error)
        {
            // Graceful fallback: возвратить безопасный результат
            $this->logger->error("Ошибка получения данныx от AI", ['error' => $error->getMessage()]);
            return new AiAnalysisDTO('neutral', 'other', null);
        }
    }
}
