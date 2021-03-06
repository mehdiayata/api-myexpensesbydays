<?php 

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\BudgetRepository;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class GetBudgetByWallet extends AbstractController
{
    public function __invoke(Wallet $data, Request $request, BudgetRepository $budgetRepository)
    {   
            
        return $budgetRepository->findByWallet( $data->getId());

    }
}