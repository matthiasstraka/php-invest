<?php

namespace App\Repository;

use App\Entity\AssetClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssetClass|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetClass|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetClass[]    findAll()
 * @method AssetClass[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetClass::class);
    }

    // /**
    //  * @return AssetClass[] Returns an array of AssetClass objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AssetClass
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
