<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\Instrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function getInstrumentPositionsForUser(Asset $asset, UserInterface $user)
    {
        // see https://stackoverflow.com/questions/53867867/doctrine-query-builder-inner-join-with-subselect
        $sql = <<<SQL
            WITH asset_instruments AS (
                SELECT * FROM instrument WHERE underlying_id = :asset AND status IN (:validstatus)
            )
            SELECT i.*, sub.units, sub.totalvalue
            FROM asset_instruments i
            LEFT JOIN (
                SELECT e.instrument_id, sum(e.volume * e.direction) AS units, SUM(e.price * e.volume * e.direction ) AS totalvalue
                FROM execution e
                    INNER JOIN account_transaction t ON t.id = e.transaction_id
                    INNER JOIN account a ON a.id = t.account_id
                WHERE a.owner_id = :user AND e.instrument_id IN (SELECT id FROM asset_instruments)
                GROUP BY e.instrument_id
                ) sub ON sub.instrument_id = i.id
        SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Instrument', 'i');
        $rsm->addScalarResult('units', 'units');
        $rsm->addScalarResult('totalvalue', 'totalvalue');

        $q = $this->_em->createNativeQuery($sql, $rsm)
            ->setParameter('asset', $asset)
            ->setParameter('validstatus', [Instrument::STATUS_ACTIVE, Instrument::STATUS_BARRIER_BREACHED])
            ->setParameter('user', $user)
        ;

        return $q->getResult();
    }

    // /**
    //  * @return Asset[] Returns an array of Asset objects
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
    public function findOneBySomeField($value): ?Asset
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
