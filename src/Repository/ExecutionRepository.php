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
                asset.country as asset_country,
                asset.currency as asset_currency,
                SUM(e.volume * e.direction) as units,
                SUM(e.price * e.volume * e.direction) AS value_total,
                ap.close * COALESCE(i.ratio, 1) * SUM(e.volume * e.direction) AS value_underlying
            FROM App\Entity\Execution e
            JOIN App\Entity\Transaction t WITH t.id = e.transaction
            JOIN App\Entity\Account a WITH a.id = t.account
            JOIN App\Entity\Instrument i WITH i.id = e.instrument
            JOIN App\Entity\Asset asset WITH asset.id = i.underlying
            LEFT JOIN App\Entity\AssetPrice ap
                WITH ap.asset = i.underlying
                AND ap.date = (SELECT MAX(ap2.date) FROM App\Entity\AssetPrice ap2 WHERE ap2.asset = i.underlying)
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

    public function getPositionsForAccount(Account $account, bool $show_empty = False)
    {
        $q = $this->_em->createQueryBuilder()
            ->select(
                'i as instrument',
                'asset.id as assetid',
                'asset.name as assetname',
                'asset.symbol as assetsymbol',
                'asset.country as assetcountry',
                'SUM(e.volume * e.direction) as units',
                'SUM(e.price * e.volume * e.direction) AS totalvalue'
            )
            ->from('App\Entity\Transaction', 't')
            ->innerJoin('App\Entity\Execution', 'e', Join::WITH, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Instrument', 'i', Join::WITH, 'i.id = e.instrument')
            ->innerJoin('App\Entity\Asset', 'asset', Join::WITH, 'asset.id = i.underlying')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->groupBy('e.instrument');
        
        if (!$show_empty)
        {
            $q->having('SUM(e.volume * e.direction) != 0');
        }

        return $q->getQuery()->getResult();
    }

    public function getInstrumentTransactionsForUser(UserInterface $user, Instrument $instrument)
    {
        $q = $this->_em->createQueryBuilder()
            ->select(
                't.id AS transaction',
                't.time AS time',
                't.notes AS notes',
                'e.volume AS volume',
                'e.price AS price',
                'e.price * e.volume AS total',
                '-1 * (COALESCE(t.tax, 0) + COALESCE(t.commission, 0) + COALESCE(t.interest, 0)) AS costs',
                'e.direction AS direction',
                't.external_id AS external_id',
                'a.name AS accountname',
                'a.id AS accountid',
            )
            ->from('App\Entity\Transaction', 't')
            ->innerJoin('App\Entity\Execution', 'e', Join::WITH, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Account', 'a', Join::WITH, 't.account = a.id')
            ->where('a.owner = :user')
            ->andWhere('e.instrument = :instrument')
            ->setParameter('user', $user)
            ->setParameter('instrument', $instrument)
            ->getQuery();
        return $q->getResult();
    }

    public function getInstrumentPositionsForUser(UserInterface $user, Instrument $instrument)
    {
        $q = $this->_em->createQueryBuilder()
            ->select(
                't.id AS transaction',
                't.time AS time',
                't.notes AS notes',
                'e.volume AS volume',
                'e.price AS price',
                'e.direction AS direction',
                't.external_id AS external_id',
                'a.name AS accountname',
                'a.id AS accountid',
            )
            ->from('App\Entity\Execution', 'e')
            ->innerJoin('App\Entity\Transaction', 't', Join::WITH, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Account', 'a', Join::WITH, 't.account = a.id')
            ->where('a.owner = :user')
            ->andWhere('e.instrument = :instrument')
            ->setParameter('user', $user)
            ->setParameter('instrument', $instrument)
            ->getQuery();
        return $q->getResult();
    }

    public function getAccountTrades(Account $account)
    {
        $q = $this->_em->createQueryBuilder()
            ->select(
                't.id AS id',
                't.time AS time',
                't.external_id AS external_id',
                't.notes AS notes',
                'e.volume AS volume',
                'e.direction AS direction',
                'e.price AS price',
                '(COALESCE(t.tax, 0) + COALESCE(t.commission, 0) + COALESCE(t.interest, 0) - e.price * e.direction * e.volume) AS total',
                'i.id AS instrument_id',
                'i.name AS instrument_name',
                'i.isin AS instrument_isin',
                'i.currency AS instrument_currency',
            )
            ->from('App\Entity\Execution', 'e')
            ->innerJoin('App\Entity\Transaction', 't', Join::WITH, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Instrument', 'i', Join::WITH, 'e.instrument = i.id')
            ->where('t.account = :account')
            ->orderBy('t.time', 'DESC')
            ->setMaxResults(100)
            ->setParameter('account', $account)
            ->getQuery();
        return $q->getResult();
    }
}
