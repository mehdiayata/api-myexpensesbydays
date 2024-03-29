<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\BudgetRepository;
use App\Repository\WalletRepository;
use App\Service\CalculService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[AsController]
class TransactionDeleteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private WalletRepository $walletRepository, private BudgetRepository $budgetRepository)
    {
        
    }
    public function __invoke(Transaction $data, Request $request): Transaction
    {
        $calculService = new CalculService();

        // Update l'amount et le savingReal après un Delete
        $wallet = $data->getWallet();
        
        $result = $wallet->getAmount() - $data->getAmount();
        $savingReal = $calculService->calculNewSavingRealDelete($wallet->getSavingReal(), $data->getAmount());
        $authorizedExpenses = $this->calculAuthorizedExpenses($wallet, $savingReal);
        
        $wallet->setAmount($result);
        $wallet->setSavingReal($savingReal);
        $wallet->setAuthorizedExpenses($authorizedExpenses);

        $this->em->persist($wallet);
        $this->em->flush();

        return $data;
    }

    public function calculAuthorizedExpenses($wallet, $newSavingReal)
    {
        $calculService = new CalculService();
        $sumAmountCoast = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 1);
        $sumAmountIncome = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 0);
    
        return $calculService->calculAuthorizedExpenses($sumAmountIncome, $sumAmountCoast, $wallet->getSaving(), $newSavingReal);
    }

}
