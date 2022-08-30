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


    public function findCoastByWallet($idWallet)
    {

        return $this->createQueryBuilder('b')
            ->where('b.wallet = :wallet')
            ->andWhere('b.coast = :coast')
            ->setParameter('wallet', $idWallet)
            ->setParameter('coast', 1)
            ->getQuery()
            ->getResult();
    }

    public function findIncomeByWallet($idWallet)
    {

        return $this->createQueryBuilder('b')
            ->where('b.wallet = :wallet')
            ->andWhere('b.coast = :coast')
            ->setParameter('wallet', $idWallet)
            ->setParameter('coast', 0)
            ->getQuery()
            ->getResult();
    }

    public function findSumBudgetByWallet($idWallet, $isCoast)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT SUM((amount*  (CHAR_LENGTH (budget.due_date) - CHAR_LENGTH (REPLACE(budget.due_date,\',\',\'\')) + 0))) as cnt  
        FROM budget 
        WHERE wallet_id = :wallet_id  AND coast = :is_coast
            ';





        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['wallet_id' => $idWallet, 'is_coast' => $isCoast]);


        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchOne();
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
