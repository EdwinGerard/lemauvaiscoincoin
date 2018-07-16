<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByLike($name, $filterName, $filter)
    {
        if ($filterName === '' && $filter === '') {
            return $this->createQueryBuilder('p')
                ->where('p.name LIKE :name')
                ->setParameter('name', "%$name%")
                ->getQuery()
                ->execute();
        }
        if ($filterName == 'category') {
            return $this->createQueryBuilder('p')
                ->join('p.category', 'c')
                ->where('p.name LIKE :name')
                ->andWhere('c.id = :idFilter')
                ->setParameter('name', "%$name%")
                ->setParameter('idFilter', "$filter")
                ->getQuery()
                ->execute();
        } elseif ($filterName == 'department') {
            return $this->createQueryBuilder('p')
                ->join('p.department', 'd')
                ->where('p.name LIKE :name')
                ->andWhere('d.id = :idFilter')
                ->setParameter('name', "%$name%")
                ->setParameter('idFilter', "$filter")
                ->getQuery()
                ->execute();
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
