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
        if ($request->get('_api_item_operation_name') == 'wallet_budget_coast') {
            return $budgetRepository->findCoastByWallet($data->getId());
        } else if ($request->get('_api_item_operation_name') == 'wallet_budget_income') {
            return $budgetRepository->findIncomeByWallet($data->getId());
        } else {
            return 0;
        }
    }
}
