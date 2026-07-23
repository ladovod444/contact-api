<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContactStatistics;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContactStatisticsRepository extends ServiceEntityRepository implements ContactStatisticsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactStatistics::class);
    }

    public function getMetrics(?DateTimeImmutable $dateFrom, ?DateTimeImmutable $dateTo): array
    {
        $qb = $this->createQueryBuilder('s');

        if($dateFrom)
        {
            $qb->andWhere('s.createdAt >= :dateFrom')->setParameter('dateFrom', $dateFrom);
        }
        if($dateTo)
        {
            $qb->andWhere('s.createdAt <= :dateTo')->setParameter('dateTo', $dateTo);
        }

        return $qb
            ->select('s.sentiment', 's.category', 'COUNT(s.id) AS total')
            ->groupBy('s.sentiment', 's.category')
            ->getQuery()
            ->getResult();
    }

}
