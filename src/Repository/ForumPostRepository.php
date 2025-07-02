<?php

namespace App\Repository;

use App\Entity\ForumPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumPost>
 */
class ForumPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumPost::class);
    }

    /**
     * Get all post ordered by id desc
     * 
     * @return array<ForumPost>
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('f')
                    ->orderBy('f.id', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    public function findOneById(int $id): ?ForumPost
    {
            return $this->createQueryBuilder('f')
               ->andWhere('f.id = :val')
               ->setParameter('val', $id)
               ->getQuery()
               ->getOneOrNullResult();
    }
    
    public function countChildren(int $parentId): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->where('f.parentId = :parentId')
            ->setParameter('parentId', $parentId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
