<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Transaction;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


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

    public function findByWallet(int $page = 1, $idWallet): Paginator
    {
        $firstResult = ($page - 1) * self::ITEMS_PER_PAGE;

        $qb = $this->createQueryBuilder('t')
            ->where('t.wallet = :wallet')
            ->setParameter('wallet', $idWallet);

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults(self::ITEMS_PER_PAGE);
        $qb->addCriteria($criteria);


        $doctrinePaginator = new DoctrinePaginator($qb);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }

    public function addTransactionByBudget()
    {
        $entityManager = $this->getEntityManager();

        // Find all budget 
        $budgetRepository = $entityManager->getRepository(Budget::class);

        $budgets = $budgetRepository->findAll();

        // Loop budget 
        foreach ($budgets as $budget) {

            if ($budget->getDueDate()) {
                foreach ($budget->getDueDate() as $dateTransaction) {
                    if ($dateTransaction == date('d')) {
                        $transaction = new Transaction();
                        $transaction->setAmount($budget->getAmount());
                        $transaction->setWallet($budget->getWallet());
                        $transaction->setCreatedAt(new \DateTime('now'));

                        $entityManager->persist($transaction);
                        $entityManager->flush();
                    }
                }
            }
        }
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
