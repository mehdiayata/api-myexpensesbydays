<?php

namespace App\EventSubscriber;

use App\Entity\Transaction;
use App\Service\CalculService;
use App\Repository\BudgetRepository;
use App\Repository\WalletRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

final class AddSavingRealSubscriber implements EventSubscriberInterface
{
    private $user;

    public function __construct(
        private WalletRepository $walletRepository,
        private BudgetRepository $budgetRepository,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $em,
        private Security $security,
        private CalculService $calculService
    ) {
        $this->user = $security->getUser();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['updateAuthrizedExpenses', EventPriorities::PRE_WRITE],
        ];
    }

    public function updateAuthrizedExpenses(ViewEvent $event)
    {
        $transaction = $event->getControllerResult();

        $method = $event->getRequest()->getMethod();
        // Si ce n'est pas un transaction et si ce n'est pas une méthode post ne fait rien
        if (!$transaction instanceof Transaction || Request::METHOD_GET == $method) {
            return;
        }

        $wallet = $transaction->getWallet();

        // Put
        if ($transaction instanceof Transaction && Request::METHOD_PUT == $method) {
            // OldData.
            $oldTransaction = $event->getRequest()->get('previous_data');

            $newSavingReal = $this->calculService->calculNewSavingRealPut($wallet->getSavingReal(), $oldTransaction->getAmount(), $transaction->getAmount());
            $wallet->setSavingReal($newSavingReal);
            $this->em->flush();
        }

        // Delete
        if ($transaction instanceof Transaction && Request::METHOD_DELETE == $method) {
            // OldData.
            $oldTransaction = $event->getRequest()->get('previous_data');

            $newSavingReal = $this->calculService->calculeNewSavingRealDelete($wallet->getSavingReal(), $oldTransaction->getAmount());

            $wallet->setSavingReal($newSavingReal);
            
            $this->em->flush();
        }

        $authorizedExpenses = $this->calculAuthorizedExpenses($wallet);

        $wallet->setAuthorizedExpenses($authorizedExpenses);

        $this->em->flush();
    }

  

    public function calculAuthorizedExpenses($wallet)
    {
        $budgets = $this->calculBudget($wallet->getId());

        return $this->calculService->calculAuthorizedExpenses($budgets['sumIncome'], $budgets['sumCoast'], $wallet->getSaving(), $wallet->getSavingReal());
    }

    // Return les sommes du budget (coast et income)
    public function calculBudget($walletId)
    {
        $sumAmountCoast = $this->budgetRepository->findSumBudgetByWallet($walletId, 1);
        $sumAmountIncome = $this->budgetRepository->findSumBudgetByWallet($walletId, 0);
       
        return $budget = [
            'sumCoast' => $sumAmountCoast,
            'sumIncome' => $sumAmountIncome
        ];
    }
}
