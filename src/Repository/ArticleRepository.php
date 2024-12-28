<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findAllPaginated(int $currentPage, int $elementLimit): Paginator {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($currentPage - 1) * $elementLimit)
            ->setMaxResults($elementLimit);

        return new Paginator($query, true);
    }

    public function findByTagPaginated(Tag $tag, int $currentPage, int $elementLimit): Paginator {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->join('a.tags', 't')
            ->andWhere('t = :tag')
            ->setFirstResult(($currentPage - 1) * $elementLimit)
            ->setMaxResults($elementLimit)
            ->setParameter('tag', $tag)
        ;

        return new Paginator($query, true);
    }

}
