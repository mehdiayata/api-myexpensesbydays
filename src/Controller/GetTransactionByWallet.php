<?php 

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class GetTransactionByWallet extends AbstractController
{
    public function __invoke(Wallet $data, Request $request, TransactionRepository $transactionRepository): Paginator
    {   
        $page = (int) $request->query->get('page', 1);
            
        return $transactionRepository->findByWallet($page, $data->getId());


    }
}