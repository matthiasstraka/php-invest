<?php

namespace App\Repository;

use App\Entity\Execution;
use App\Entity\Instrument;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getPositionsForUser(User $user, bool $show_empty = False)
    {
        $q = $this->_em->createQueryBuilder()
            ->select(
                'i as instrument',
                'a.id as accountid',
                'a.name as accountname',
                'asset.id as assetid',
                'asset.name as assetname',
                'asset.symbol as assetsymbol',
                'SUM(e.volume * e.direction) as units',
                'SUM(e.price * e.volume * e.direction) AS totalvalue'
            )
            ->from('App\Entity\Account', 'a')
            ->innerJoin('App\Entity\Transaction', 't', Join::WITH, 't.account = a.id')
            ->innerJoin('App\Entity\Execution', 'e', Join::WITH, 'e.transaction = t.id')
            ->innerJoin('App\Entity\Instrument', 'i', Join::WITH, 'i.id = e.instrument')
            ->innerJoin('App\Entity\Asset', 'asset', Join::WITH, 'asset.id = i.underlying')
            ->where('a.owner = :user')
            ->setParameter('user', $user)
            ->groupBy('e.instrument');
        
        if (!$show_empty)
        {
            $q->having('SUM(e.volume * e.direction) != 0');
        }

        return $q->getQuery()->getResult();
    }

    
    public function getInstrumentTransactionsForUser(User $user, Instrument $instrument)
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

    public function getInstrumentPositionsForUser(User $user, Instrument $instrument)
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
}
