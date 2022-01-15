<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\Instrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
        $q = $this->_em->createQueryBuilder()
            ->select(
                'i as instrument',
                'SUM(e.volume * e.direction) as units',
                'SUM(e.price * e.volume * e.direction) AS totalvalue'
            )
            ->from('App\Entity\Instrument', 'i')
            ->leftJoin('App\Entity\Execution', 'e', Join::WITH, 'e.instrument = i.id')
            //->leftJoin('App\Entity\Transaction', 't', Join::WITH, 't.id = e.transaction')
            //->leftJoin('App\Entity\Account', 'a', Join::WITH, 'a.id = t.account')
            ->where('i.underlying = :asset')
            ->andWhere('i.status IN (:validstatus)')
            //->andWhere('a.owner = :user')
            ->setParameter('asset', $asset)
            ->setParameter('validstatus', [Instrument::STATUS_ACTIVE, Instrument::STATUS_BARRIER_BREACHED])
            //->setParameter('user', $user)
            ->groupBy('i.id');
        // TODO: Only show positions for the current user
        return $q->getQuery()->getResult();
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
