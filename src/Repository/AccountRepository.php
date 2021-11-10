<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function getBalances(User $user)
    {
        $q = $this->createQueryBuilder('a')
            ->select(
                'a.id',
                '(COALESCE(SUM(t.portfolio), 0) + COALESCE(SUM(t.cash), 0) + COALESCE(SUM(t.commission), 0) + COALESCE(SUM(t.tax), 0) + COALESCE(SUM(t.interest), 0) + COALESCE(SUM(t.dividend), 0) + COALESCE(SUM(t.consolidation), 0)) as balance')
            ->leftJoin('App\Entity\Transaction', 't', Join::WITH, 'a.id = t.account')
            ->where('a.owner = :user')
            ->setParameter('user', $user)
            ->groupBy('a.id')
            ->getQuery();
        return $q->getResult();
    }
}
