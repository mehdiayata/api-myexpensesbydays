<?php

namespace App\Repository;

use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository
{
    const ITEMS_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    
    public function findByWallet($idWallet)
    {

        return $this->createQueryBuilder('b')
            ->where('b.wallet = :wallet')
            ->andWhere('b.coast = :coast')
            ->setParameter('wallet', $idWallet)
            ->setParameter('coast', 1)
            ->getQuery()
            ->getResult();

    }

    // public function findByWallet(int $page = 1, $idWallet): Paginator
    // {
    //     $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

    //     $qb = $this->createQueryBuilder('b')
    //         ->where('b.wallet = :wallet')
    //         ->setParameter('wallet', $idWallet);

    //     $criteria = Criteria::create()
    //         ->setFirstResult($firstResult)
    //         ->setMaxResults(self::ITEMS_PER_PAGE);
    //     $qb->addCriteria($criteria);


    //     $doctrinePaginator = new DoctrinePaginator($qb);
    //     $paginator = new Paginator($doctrinePaginator);

    //     return $paginator;
    // }

}
