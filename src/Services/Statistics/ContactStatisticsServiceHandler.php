<?php
declare(strict_types=1);

namespace App\Services\Statistics;

use App\DTO\AiAnalysisDTO;
use App\DTO\ContactDTO;
use App\Entity\ContactStatistics;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Получает данные от аналитики и запроса создает сущность ContactStatistics
 */
class ContactStatisticsServiceHandler implements ContactStatisticsServiceInterface
{

    function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function createStatistics(AiAnalysisDTO $analysisDTO, ContactDTO $contactDTO, string $ip): ContactStatistics
    {

        $contactStatistics = new ContactStatistics();
        $contactStatistics
            ->setIp($ip)
            ->setName($contactDTO->getName())
            ->setEmail($contactDTO->getEmail())
            ->setPhone($contactDTO->getPhone())
            ->setSentiment($analysisDTO->getSentiment())
            ->setCategory($analysisDTO->getCategory())
            ->setAutoReply($analysisDTO->getAutoReply())
            ->setComment($contactDTO->getComment());

        $this->entityManager->persist($contactStatistics);
        $this->entityManager->flush();

        return $contactStatistics;
    }
}
