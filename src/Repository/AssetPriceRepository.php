<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AssetPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetPrice[]    findAll()
 * @method AssetPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetPrice::class);
    }

    public function latestPrice(Asset $asset): ?AssetPrice
    {
        $q = $this->createQueryBuilder('ap')
            ->where('ap.asset = :aid')
            ->andWhere('ap.date = (SELECT MAX(ap2.date) FROM App\Entity\AssetPrice ap2 WHERE ap2.asset = :aid)')
            ->setParameter('aid', $asset);
        return $q->getQuery()->getOneOrNullResult();
    }
}
