<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template-extends Post
 */
final class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPaginatedPosts(int $page, int $nbPerPage = 18): Paginator
    {
        $queryBuilder =  $this->createQueryBuilder('p')
            ->addSelect('c')
            ->addSelect('t')
            ->addSelect('u')
            ->join('p.category', 'c')
            ->join('p.tags', 't')
            ->join('p.user', 'u')
            ->orderBy('p.publishedAt', 'desc')
            ->setMaxResults($nbPerPage)
            ->setFirstResult(($page -1) * $nbPerPage)
            ->groupBy('p.id');

        return new Paginator($queryBuilder);
    }
}
