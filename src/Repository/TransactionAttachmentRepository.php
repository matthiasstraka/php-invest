<?php

namespace App\Repository;

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
}
