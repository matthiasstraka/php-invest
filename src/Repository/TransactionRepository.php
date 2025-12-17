<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getAccountTransactions(Account $account)
    {
        $qb = $this->getEntityManager()->createQueryBuilder(); 
        $q = $qb
            ->select('t')
            ->from('App\Entity\Transaction', 't')
            ->leftJoin('App\Entity\Execution', 'e', Join::WITH, 't.id = e.transaction')
            ->where('t.account = :account')
            ->andWhere('e.transaction IS NULL')
            ->setParameter('account', $account)
            ->getQuery();
        return $q->getResult();
    }

    
    public function getAccountBalance(Account $account)
    {
        $q = $this->createQueryBuilder('t')
            ->select(
                '(COALESCE(SUM(t.portfolio), 0) + COALESCE(SUM(t.cash), 0) + COALESCE(SUM(t.commission), 0) + COALESCE(SUM(t.tax), 0) + COALESCE(SUM(t.interest), 0) + COALESCE(SUM(t.consolidation), 0)) as balance')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->getQuery();
        return $q->getSingleScalarResult();
    }

    // /**
    //  * @return Transaction[] Returns an array of Transaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
