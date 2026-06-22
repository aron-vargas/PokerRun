<?php

namespace App\Repository;

use App\Entity\PlayerLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerLocation>
 */
class PlayerLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerLocation::class);
    }

    public function findAllUnvisitedCardStopsForPlayer(int $playerId): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.card_stop', 'c')
            ->leftJoin('l.player', 'p')
            ->leftJoin('p.location_id', 'pl', 'WITH', 'pl.Player = :playerId AND c.id = pl.card_stop_id AND pl.isVerified = 1')
            ->andWhere('pl.Player IS NULL')
            ->andWhere('l.isVerified = 0')
            ->setParameter('playerId', $playerId);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return PlayerLocation[] Returns an array of PlayerLocation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PlayerLocation
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
