<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Execution;
use App\Entity\Instrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Execution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Execution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Execution[]    findAll()
 * @method Execution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExecutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Execution::class);
    }

    public function getPositionsForUser(UserInterface $user, bool $show_empty = False)
    {
        $dql = <<<SQL
            SELECT 
                i as instrument,
                a.id as account_id,
                a.name as account_name,
                asset.id as asset_id,
                asset.name as asset_name,
                asset.symbol as asset_symbol,
                asset.type as asset_type,
                asset.country as asset_country,
                asset.currency as asset_currency,
                SUM(e.volume * e.direction) as units,
                SUM(e.price * e.volume * e.direction / e.exchange_rate) AS value_total,
                ap.close * COALESCE(it.ratio, 1) * SUM(e.volume * e.direction) AS value_underlying
            FROM App\Entity\Execution e
            JOIN App\Entity\Transaction t ON t.id = e.transaction
            JOIN App\Entity\Account a ON a.id = t.account
            JOIN App\Entity\Instrument i ON i.id = e.instrument
            JOIN App\Entity\Asset asset ON asset.id = i.underlying
            LEFT JOIN App\Entity\AssetPrice ap
                ON ap.asset = i.underlying
                AND ap.date = (SELECT MAX(ap2.date) FROM App\Entity\AssetPrice ap2 WHERE ap2.asset = i.underlying)
            LEFT JOIN App\Entity\InstrumentTerms it
                ON it.instrument = e.instrument
                AND it.date = (SELECT MAX(it2.date) FROM App\Entity\InstrumentTerms it2 WHERE it2.instrument = e.instrument)
            WHERE a.owner = :user
            GROUP BY e.instrument
        SQL;

        if (!$show_empty)
        {
            $dql = $dql. " HAVING SUM(e.volume * e.direction) != 0";
        }

        $q = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('user', $user);

        return $q->getResult();
    }

    public function getOpenPositionAssetIdsForUser(UserInterface $user)
    {
        $dql = <<<SQL
            SELECT DISTINCT IDENTITY(i.underlying)
            FROM App\Entity\Execution e
            JOIN App\Entity\Transaction t ON t.id = e.transaction
            JOIN App\Entity\Account a ON a.id = t.account
            JOIN App\Entity\Instrument i ON i.id = e.instrument
            WHERE a.owner = :user
            GROUP BY i
            HAVING SUM(e.volume * e.direction) != 0
        SQL;

        $q = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('user', $user);

        return array_map(fn($val) => $val[1], $q->getResult());
    }

    public function getPositionsForAccount(Account $account, bool $show_empty = False)
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->select(
                'i as instrument',
                'asset.id as assetid',
                'asset.name as assetname',
                'asset.symbol as assetsymbol',
                'asset.country as assetcountry',
                'SUM(e.volume * e.direction) as units',
                'SUM(e.price * e.volume * e.direction / e.exchange_rate) AS totalvalue'
            )
            ->from('App\Entity\Transaction', 't')
            ->innerJoin('App\Entity\Execution', 'e', Join::ON, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Instrument', 'i', Join::ON, 'i.id = e.instrument')
            ->innerJoin('App\Entity\Asset', 'asset', Join::ON, 'asset.id = i.underlying')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->groupBy('e.instrument');
        
        if (!$show_empty)
        {
            $q->having('SUM(e.volume * e.direction) != 0');
        }

        return $q->getQuery()->getResult();
    }

    public function getInstrumentTransactionsForUser(UserInterface $user, Instrument $instrument, $sorted = false)
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->select(
                't.id AS transaction',
                't.time AS time',
                't.notes AS notes',
                'e.volume AS volume',
                'e.currency AS currency',
                'e.price AS price',
                'e.price * e.volume / e.exchange_rate AS total',
                '-1 * (COALESCE(t.tax, 0) + COALESCE(t.commission, 0) + COALESCE(t.interest, 0)) AS costs',
                'e.direction AS direction',
                'e.type AS execution_type',
                't.transaction_id AS transaction_id',
                't.consolidated AS consolidated',
                'a.name AS account_name',
                'a.id AS account_id',
                'a.currency AS account_currency',
            )
            ->from('App\Entity\Transaction', 't')
            ->innerJoin('App\Entity\Execution', 'e', Join::ON, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Account', 'a', Join::ON, 't.account = a.id')
            ->where('a.owner = :user')
            ->andWhere('e.instrument = :instrument')
            ->setParameter('user', $user)
            ->setParameter('instrument', $instrument);
        if ($sorted)
        {
            $q = $q->orderBy('t.time');
        }
        return $q->getQuery()->getResult();
    }

    public function getAccountTrades(Account $account)
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->select(
                't.id AS id',
                't.time AS time',
                't.transaction_id AS transaction_id',
                't.notes AS notes',
                't.consolidated AS consolidated',
                'e.volume AS volume',
                'e.direction AS direction',
                'e.currency AS execution_currency',
                'e.price AS price',
                'e.execution_id AS execution_id',
                'e.type AS execution_type',
                't.tax as tax',
                't.interest as interest',
                't.commission as commission',
                '0 + COALESCE(t.portfolio, 0) + COALESCE(t.cash, 0) + COALESCE(t.tax, 0) + COALESCE(t.interest, 0) + COALESCE(t.commission, 0) AS cashflow',
                'i.id AS instrument_id',
                'i.name AS instrument_name',
                'i.isin AS instrument_isin',
                'i.currency AS instrument_currency',
            )
            ->from('App\Entity\Execution', 'e')
            ->innerJoin('App\Entity\Transaction', 't', Join::ON, 'e.transaction = t.id')
            ->leftJoin('App\Entity\Instrument', 'i', Join::ON, 'e.instrument = i.id')
            ->where('t.account = :account')
            ->orderBy('t.time', 'DESC')
            ->setMaxResults(100)
            ->setParameter('account', $account)
            ->getQuery();
        return $q->getResult();
    }
}
