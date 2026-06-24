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

    public function countCheckIns(): int
    {
        return (int) $this->createQueryBuilder('pl')
            ->select('COUNT(pl.id)')
            ->andWhere('pl.checkin_time IS NOT NULL')
            ->andWhere('pl.isVerified = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentActivity(int $limit = 10): array
    {
        $rows = $this->createQueryBuilder('pl')
            ->select('u.firstName AS firstName', 'u.lastName AS lastName', 'pl.checkin_time AS checkinTime', 'cs.card_stop_name AS location')
            ->join('pl.Player', 'u')
            ->leftJoin('pl.CardStop', 'cs')
            ->andWhere('pl.checkin_time IS NOT NULL')
            ->orderBy('pl.checkin_time', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(function (array $row) {
            return [
                'playerName' => trim(sprintf('%s %s', $row['firstName'] ?? '', $row['lastName'] ?? '')),
                'checkInTime' => $row['checkinTime'],
                'location' => $row['location'] ?? 'Unknown',
            ];
        }, $rows);
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
