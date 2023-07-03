<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\TransactionAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransactionAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionAttachment[]    findAll()
 * @method TransactionAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionAttachment::class);
    }

    public function getTransactionAttachments(Transaction $transaction)
    {
        $qb = $this->_em->createQueryBuilder(); 
        $q = $qb
            ->select(['ta.id', 'ta.name', 'ta.time_uploaded', 'length(ta.content) as size'])
            ->from('App\Entity\TransactionAttachment', 'ta')
            ->where('ta.transaction = :transaction')
            ->setParameter('transaction', $transaction)
            ->getQuery();
        return $q->getResult();
    }
}
