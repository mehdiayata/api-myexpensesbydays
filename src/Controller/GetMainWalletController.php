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
class GetMainWalletController extends AbstractController
{
    public function __construct(private WalletRepository $walletRepository, private Security $security, private EntityManagerInterface $em) {}

    public function __invoke(): Wallet
    {

        return $this->getMainWallet();
    }

    public function getMainWallet() {
        
        $mainWallet = $this->walletRepository->findOneBy([
            'main' => 1,
            'owner' => $this->security->getUser()
        ]);
        
        return $mainWallet;
    }
}
