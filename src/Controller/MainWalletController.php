<?php

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[AsController]
class MainWalletController extends AbstractController
{
    public function __construct(private WalletRepository $walletRepository, private Security $security, private EntityManagerInterface $em) {}

    public function __invoke(Wallet $data, Request $request): Wallet
    {
        $this->editOldMainWallet();
        $data->setMain(true);

        return $data;
    }

    public function editOldMainWallet() {
        
        $oldMainWallet = $this->walletRepository->findOneBy([
            'main' => 1,
            'owner' => $this->security->getUser()
        ]);
        
        $oldMainWallet->setMain(false);

        
        $this->em->persist($oldMainWallet);
        $this->em->flush();
    }
}
