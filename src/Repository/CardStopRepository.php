<?php

namespace App\Repository;

use App\Entity\CardStop;
use App\Entity\PlayerLocation;
use App\Repository\UserRepository;  
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<CardStop>
 */
class CardStopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardStop::class);
    }

    public function findAllUnvisitedCardStopsForPlayerQB(int $playerId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin(PlayerLocation::class, 'pl', 'WITH', 'c.id = pl.CardStop AND pl.Player = :playerId AND pl.isVerified = 1')
            ->andWhere('pl.Player IS NULL')
            ->setParameter('playerId', $playerId);

        return $qb;
    }

    //    /**
    //     * @return CardStop[] Returns an array of CardStop objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CardStop
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
