<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Movie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Movie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Search in movies.
     *
     * @param int $page Collection page to return.
     * @param int $size Collection size to return.
     * @param string|null $search Search with a term.
     * @return mixed
     */
    public function search(int $page, int $size, string $search = null): mixed
    {
        return $this->createQueryBuilder('m')
            ->where('m.title LIKE :search')
            ->orWhere('m.description LIKE :search')
            ->orWhere('m.releasedAt LIKE :search')
            ->orWhere('m.note LIKE :search')
            ->setParameter('search', '%'. $search .'%')
            ->setFirstResult($size * ($page-1))
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search in movies with a specific category.
     *
     * @param int $category_id Category id.
     * @param int $page Collection page to return.
     * @param int $size Collection size to return.
     * @param string|null $search Search with a term.
     * @return mixed
     */
    public function searchByCategory(int $category_id, int $page, int $size, string $search = null): mixed
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.categories', 'c', 'WITH', 'c = :category_id')
            ->orWhere('m.title LIKE :search')
            ->orWhere('m.description LIKE :search')
            ->orWhere('m.releasedAt LIKE :search')
            ->orWhere('m.note LIKE :search')
            ->setParameter('category_id', $category_id)
            ->setParameter('search', '%'. $search .'%')
            ->setFirstResult($size * ($page-1))
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }
}
