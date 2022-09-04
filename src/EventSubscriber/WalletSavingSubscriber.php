<?php

namespace App\EventSubscriber;

use App\Entity\Budget;
use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Service\CalculService;
use App\Repository\BudgetRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class WalletSavingSubscriber implements EventSubscriberInterface
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
        if (!$eventEntity instanceof Wallet || Request::METHOD_GET == $method) {
                return;
        } 
        
        // POST
        if ($eventEntity instanceof Wallet && Request::METHOD_POST == $method) {
            return;
        }

        // Put
        if ($eventEntity instanceof Wallet && Request::METHOD_PUT == $method) {
            // OldData
            $oldWallet = $event->getRequest()->get('previous_data');

            if($oldWallet->getSaving() != $eventEntity->getSaving()) {
                
                
                $authorizedExpenses = $this->calculAuthorizedExpenses($eventEntity);

                $eventEntity->setAuthorizedExpenses($authorizedExpenses);

                $this->em->flush();
            } else {
                return;
            }

        } 
    }


  

    public function calculAuthorizedExpenses($wallet)
    {
        
        $sumAmountCoast = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 1);
        $sumAmountIncome = $this->budgetRepository->findSumBudgetByWallet($wallet->getId(), 0);
    

        return $this->calculService->calculAuthorizedExpenses($sumAmountIncome, $sumAmountCoast, $wallet->getSaving(), $wallet->getSavingReal());
    }

   
}
