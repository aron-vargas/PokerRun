<?php

namespace App\Repository;

use App\Entity\PlayerLocation;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerLocation>
 */
class PlayerLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private UserRepository $userRepo)
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
            ->andWhere('pl.checkinTime IS NOT NULL')
            ->andWhere('pl.isVerified = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPurchases(): int
    {
        return (int) $this->createQueryBuilder('pl')
            ->select('COUNT(pl.id)')
            ->andWhere('pl.purchaseTime IS NOT NULL')
            ->andWhere('pl.isVerified = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
    * @param integer $value Location ID
    * @return PlayerLocation Returns one objects
    */
    public function findOneById($value): ?PlayerLocation
    {
        return $this->createQueryBuilder('pl')
            ->andWhere('pl.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecentActivity(int $limit = 10): array
    {
        $rows = $this->createQueryBuilder('pl')
            ->select('u.firstName AS firstName', 'u.lastName AS lastName', 'pl.checkinTime AS checkinTime', 'cs.card_stop_name AS location')
            ->join('pl.Player', 'u')
            ->leftJoin('pl.CardStop', 'cs')
            ->andWhere('pl.checkinTime IS NOT NULL')
            ->orderBy('pl.checkinTime', 'DESC')
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

    public function findRecentPurchases(int $limit = 10): array
    {
        $rows = $this->createQueryBuilder('pl')
            ->select('u.firstName AS firstName', 'u.lastName AS lastName', 'pl.checkinTime AS checkinTime', 'cs.card_stop_name AS location')
            ->join('pl.Player', 'u')
            ->leftJoin('pl.CardStop', 'cs')
            ->andWhere('pl.purchaseTime IS NOT NULL')
            ->orderBy('pl.purchaseTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(function (array $row)
        {
            return [
                'playerName' => trim(sprintf('%s %s', $row['firstName'] ?? '', $row['lastName'] ?? '')),
                'purchaseTime' => $row['purchaseTime'],
                'location' => $row['location'] ?? 'Unknown',
            ];
        }, $rows);
    }

    public function findCardStopUnverified(int $card_stop_id, int $limit=100): array
    {
        $rows = $this->createQueryBuilder('pl')
            ->select('pl.id AS id', 'u.id AS player_id', 'u.firstName AS firstName', 'u.lastName AS lastName', 'pl.checkinTime AS checkinTime', 'cs.card_stop_name AS location')
            ->join('pl.Player', 'u')
            ->leftJoin('pl.CardStop', 'cs')
            ->andWhere('pl.isVerified = 0')
            ->andWhere('cs.id = :stop')
            ->setParameter('stop', $card_stop_id)
            ->orderBy('pl.checkinTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findCardStopNoPurchase(int $card_stop_id, int $limit = 100): array
    {
        $rows = $this->createQueryBuilder('pl')
            ->select('pl.id AS id', 'u.id AS player_id', 'u.firstName AS firstName', 'u.lastName AS lastName', 'pl.checkinTime AS checkinTime', 'cs.card_stop_name AS location')
            ->join('pl.Player', 'u')
            ->leftJoin('pl.CardStop', 'cs')
            ->andWhere('pl.isVerified = 1')
            ->andWhere('pl.purchaseTime is null')
            ->andWhere('cs.id = :stop')
            ->setParameter('stop', $card_stop_id)
            ->orderBy('pl.checkinTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function removeLocation(PlayerLocation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function verifyLocation(PlayerLocation $entity, int $user_id, bool $flush = true): void
    {
        $entity->setIsVerified(true);
        $entity->setVerifiedOn(new DateTime());
        $entity->setVerifiedBy($this->userRepo->find($user_id));

        if ($flush)
            $this->getEntityManager()->flush();
    }
}
