<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Service\CalculService;
use App\Repository\BudgetRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[AsController]
class BudgetDeleteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private WalletRepository $walletRepository, private BudgetRepository $budgetRepository)
    {
        
    }
    public function __invoke(Budget $data, Request $request): Budget
    {

        // Update l'amount et le savingReal après un Delete
        $wallet = $data->getWallet();
        
        $authorizedExpenses = $this->calculAuthorizedExpenses($wallet, $data);

        $wallet->setAuthorizedExpenses($authorizedExpenses);

        $this->em->persist($wallet);
        $this->em->flush();

        return $data;
    }

    
    public function calculAuthorizedExpenses($wallet, $budget)
    {
      
        $calculService = new CalculService();
        $sumAmountCoast = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 1);
        $sumAmountIncome = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 0);

        $sumBudgetAmount = $budget->getAmount() * count($budget->getDueDate());

        if($budget->getCoast() == 1) {
            $sumAmountCoast -= $sumBudgetAmount;
        } else {
            $sumAmountIncome += $sumBudgetAmount;
        }
    
        return $calculService->calculAuthorizedExpenses($sumAmountIncome, $sumAmountCoast, $wallet->getSaving(), $wallet->getSavingReal());
    }
}
