<?php

namespace App\Repository;

use App\Entity\Instrument;
use App\Entity\InstrumentTerms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InstrumentTerms|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstrumentTerms|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstrumentTerms[]    findAll()
 * @method InstrumentTerms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstrumentTermsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstrumentTerms::class);
    }

    public function latestTerms(Instrument $instrument): ?InstrumentTerms
    {
        $dql = <<<SQL
            SELECT t
            FROM App\Entity\InstrumentTerms t
            WHERE t.instrument = :instrument
                AND t.date = (SELECT MAX(t2.date) FROM App\Entity\InstrumentTerms t2 WHERE t2.instrument = :instrument)
        SQL;
        $q = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('instrument', $instrument);
        return $q->getOneOrNullResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(InstrumentTerms $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(InstrumentTerms $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return InstrumentTerms[] Returns an array of InstrumentTerms objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InstrumentTerms
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
