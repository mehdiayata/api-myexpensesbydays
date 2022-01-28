<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\Criteria;


/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    const ITEMS_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByWallet(int $page = 1): Paginator
    {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $qb = $this->createQueryBuilder('t')
            ->where('t.wallet = :wallet')
            ->setParameter('wallet', 223);

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE);
        $qb->addCriteria($criteria);


        $doctrinePaginator = new DoctrinePaginator($qb);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
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
