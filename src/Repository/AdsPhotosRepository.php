<?php

namespace App\Repository;

use App\Entity\AdsPhotos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AdsPhotos|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdsPhotos|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdsPhotos[]    findAll()
 * @method AdsPhotos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdsPhotosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdsPhotos::class);
    }

    // /**
    //  * @return AdsPhotos[] Returns an array of AdsPhotos objects
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
    public function findOneBySomeField($value): ?AdsPhotos
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
