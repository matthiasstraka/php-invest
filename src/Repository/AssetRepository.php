<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Execution;
use App\Entity\Instrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function allWithLatestPrice() : array
    {
        $dql = <<<SQL
            SELECT a, ap
            FROM App\Entity\Asset a
            LEFT JOIN App\Entity\AssetPrice ap
                WITH a.id = ap.asset
                AND ap.date = (SELECT MAX(ap2.date) FROM App\Entity\AssetPrice ap2 WHERE ap2.asset = a.id)
        SQL;
        $q = $this->getEntityManager()->createQuery($dql);
        $res = $q->getResult();
        // Returns an array with asset, asset-price interleaved. Move them into a single object
        $ret = [];
        for ($n = 0; $n < count($res); $n += 2)
        {
            $obj = new \stdClass;
            $obj->asset = $res[$n];
            $obj->price = $res[$n + 1];
            $ret[] = $obj;
        }
        return $ret;
    }

    public function allWithOutdatedPrice(\DateTimeInterface $filter_date, bool $only_existing)
    {
        if ($only_existing)
        {
            $dql = <<<SQL
                SELECT a, MAX(ap.date)
                FROM App\Entity\AssetPrice ap
                LEFT JOIN App\Entity\Asset a WITH a.id = ap.asset
                GROUP BY a
                HAVING MAX(ap.date) < :filterdate
            SQL;
        } else {
            $dql = <<<SQL
                SELECT a, MAX(ap.date)
                FROM App\Entity\Asset a
                LEFT JOIN App\Entity\AssetPrice ap WITH a.id = ap.asset
                GROUP BY a
                HAVING MAX(ap.date) IS NULL OR MAX(ap.date) < :filterdate
            SQL;
        }
        $q = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('filterdate', $filter_date);

        $fn = function($asset_date) {
            $asset = $asset_date[0];
            $date = $asset_date[1];
            if (!is_null($date))
            {
                $date = \DateTime::createFromFormat("Y-m-d", $date)->setTime(0,0);
            }
            return [$asset, $date];
        };
        return array_map($fn, $q->getResult());
    }

    public function portfolioWithOutdatedPrice(UserInterface $user, \DateTimeInterface $filter_date)
    {
        $repo = $this->getEntityManager()->getRepository(Execution::class);
        $portfolio_positions = $repo->getOpenPositionAssetIdsForUser($user);
        // TODO: do we need non-existing updates?
        $dql = <<<SQL
                SELECT a, MAX(ap.date)
                FROM App\Entity\Asset a
                LEFT JOIN App\Entity\AssetPrice ap WITH a.id = ap.asset
                WHERE a.id IN (:userassets)
                GROUP BY a
                HAVING MAX(ap.date) < :filterdate
            SQL;
        $q = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('filterdate', $filter_date)
            ->setParameter('userassets', $portfolio_positions);

        $fn = function($asset_date) {
            $asset = $asset_date[0];
            $date = $asset_date[1];
            if (!is_null($date))
            {
                $date = \DateTime::createFromFormat("Y-m-d", $date)->setTime(0,0);
            }
            return [$asset, $date];
        };
        return array_map($fn, $q->getResult());
    }

    public function getInstrumentPositionsForUser(Asset $asset, UserInterface $user)
    {
        // see https://stackoverflow.com/questions/53867867/doctrine-query-builder-inner-join-with-subselect
        $sql = <<<SQL
            WITH asset_instruments AS (
                SELECT * FROM instrument WHERE underlying_id = :asset
            )
            SELECT i.*, sub.units, sub.totalvalue
            FROM asset_instruments i
            LEFT JOIN (
                SELECT e.instrument_id, sum(e.volume * e.direction) AS units, SUM(e.price * e.volume * e.direction / e.exchange_rate) AS totalvalue
                FROM execution e
                    INNER JOIN account_transaction t ON t.id = e.transaction_id
                    INNER JOIN account a ON a.id = t.account_id
                WHERE a.owner_id = :user AND e.instrument_id IN (SELECT id FROM asset_instruments)
                GROUP BY e.instrument_id
                ) sub ON sub.instrument_id = i.id
        SQL;

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('App\Entity\Instrument', 'i');
        $rsm->addScalarResult('units', 'units');
        $rsm->addScalarResult('totalvalue', 'totalvalue');

        $q = $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->setParameter('asset', $asset)
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
