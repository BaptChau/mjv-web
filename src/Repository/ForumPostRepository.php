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

    /**
     * @param array<int> $parentIds
     * @return array<int,int> key = parent id, value = children count
     */
    public function countChildrenForParents(array $parentIds): array
    {
        if ($parentIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('f')
            ->select('f.parentId AS parent_id, COUNT(f.id) AS children_count')
            ->where('f.parentId IN (:parentIds)')
            ->setParameter('parentIds', $parentIds)
            ->groupBy('f.parentId')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['parent_id']] = (int) $row['children_count'];
        }

        return $counts;
    }

    /**
     * @return array<ForumPost>
     */
    public function findRootPosts(?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.parentId IS NULL')
            ->andWhere('f.title IS NOT NULL')
            ->orderBy('f.id', 'DESC');

        if ($limit !== null && $limit > 0) {
            $qb->setMaxResults($limit);
        }

        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countRootPosts(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.parentId IS NULL')
            ->andWhere('f.title IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<ForumPost>
     */
    public function findLatestRootPosts(int $limit): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.parentId is null')
            ->andWhere('f.title is not null')
            ->orderBy('f.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findChildren(int $parentId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.parentId = :parentId')
            ->setParameter('parentId', $parentId)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
