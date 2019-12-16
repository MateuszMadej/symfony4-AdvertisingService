<?php

namespace App\Repository;

use App\Entity\AdsCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AdsCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdsCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdsCategories[]    findAll()
 * @method AdsCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdsCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsCategories::class);
    }

    // /**
    //  * @return AdsCategories[] Returns an array of AdsCategories objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AdsCategories
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
