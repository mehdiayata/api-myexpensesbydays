<?php 

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class GetTransactionByWallet extends AbstractController
{
    public function __invoke(Request $request, TransactionRepository $transactionRepository): Paginator
    {   
        
        $page = (int) $request->query->get('page', 1);

        $test = $transactionRepository->findByWallet($page);

        return $test;

    }
}