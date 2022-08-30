<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[AsController]
class TransactionDeleteController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private WalletRepository $walletRepository)
    {
        
    }
    public function __invoke(Transaction $data, Request $request): Transaction
    {
        // // Permet de soustraire la somme de la transaction supprimer, à la somme du wallet de la transaction.
        // $wallet = $data->getWallet();
        // $result = $wallet->getAmount() - $data->getAmount();
        // $savinReal = $wallet->getAmount() - $data->getAmount();
        // $wallet->setAmount($result);
        // $wallet->setSavingReal($savinReal);

        // $this->em->persist($wallet);
        // $this->em->flush();

        return $data;
    }
}
