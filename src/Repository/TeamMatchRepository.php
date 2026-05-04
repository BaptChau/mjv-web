<?php

namespace App\Repository;

use App\Entity\TeamMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMatch>
 */
class TeamMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMatch::class);
    }

    /** @return TeamMatch[] */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.team', 't')
            ->addSelect('t')
            ->where('m.matchDate > :now')
            ->andWhere('m.status = :status')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('status', 'upcoming')
            ->orderBy('m.matchDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return TeamMatch[] */
    public function findLastWeekResults(): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.team', 't')
            ->addSelect('t')
            ->where('m.matchDate >= :start')
            ->andWhere('m.matchDate <= :now')
            ->andWhere('m.homeScore IS NOT NULL')
            ->setParameter('start', new \DateTimeImmutable('-7 days'))
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return TeamMatch[] */
    public function findByDate(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.team', 't')
            ->addSelect('t')
            ->where('m.matchDate >= :start')
            ->andWhere('m.matchDate <= :end')
            ->andWhere('m.homeScore IS NOT NULL')
            ->setParameter('start', $date->setTime(0, 0, 0))
            ->setParameter('end', $date->setTime(23, 59, 59))
            ->orderBy('m.matchDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
