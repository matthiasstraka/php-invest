<?php

namespace App\Repository;

use App\Entity\AssetType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssetType|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetType|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetType[]    findAll()
 * @method AssetType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetType::class);
    }

    public function findOneByName($value): ?AssetType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.Name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
