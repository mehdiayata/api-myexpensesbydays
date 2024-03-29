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
use App\Entity\Budget;
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
            KernelEvents::VIEW => ['updateAuthrizedExpenses', EventPriorities::POST_WRITE],
        ];
    }

    public function updateAuthrizedExpenses(ViewEvent $event)
    {
        $eventEntity = $event->getControllerResult();

        $method = $event->getRequest()->getMethod();
       
        // Si ce n'est pas un transaction et si ce n'est pas une méthode post ne fait rien
        if (!$eventEntity instanceof Transaction || Request::METHOD_GET == $method) {
            if (!$eventEntity instanceof Budget) {
                return;
            }
        } 

        $wallet = $eventEntity->getWallet();
        
        
        // POST
        if ($eventEntity instanceof Transaction && Request::METHOD_POST == $method) {
         
            $newSavingReal = $this->calculService->calculNewSavingRealPost($wallet->getSavingReal(), $eventEntity->getAmount());
           
            $wallet->setSavingReal($newSavingReal);
            
        }

        // Put
        if ($eventEntity instanceof Transaction && Request::METHOD_PUT == $method) {
            // OldData
            $oldTransaction = $event->getRequest()->get('previous_data');

            $newSavingReal = $this->calculService->calculNewSavingRealPut($wallet->getSavingReal(), $oldTransaction->getAmount(), $eventEntity->getAmount());
            $wallet->setSavingReal($newSavingReal);
        } 

        $authorizedExpenses = $this->calculAuthorizedExpenses($wallet);
        
        $wallet->setAuthorizedExpenses($authorizedExpenses);
        
        $this->em->flush();
    }


  

    public function calculAuthorizedExpenses($wallet)
    {
        $sumAmountCoast = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 1);
        $sumAmountIncome = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 0);

        return $this->calculService->calculAuthorizedExpenses($sumAmountIncome, $sumAmountCoast, $wallet->getSaving(), $wallet->getSavingReal());
    }

   
}
