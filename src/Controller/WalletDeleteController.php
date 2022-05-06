<?php

namespace App\Controller;

use Exception;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;

#[AsController]
class WalletDeleteController extends AbstractController
{

    public function __construct(private WalletRepository $walletRepository)
    {
    }


    public function __invoke(Wallet $data, Request $request): Wallet
    {
        $user = $this->getUser();

        // Get nbWallet for user
        $nbWallet = count($this->walletRepository->findBy(
            ['owner' => $user]
        ));


        if($nbWallet == 1) {
            throw new Exception('Unable to delete your only wallet');
        }

        return $data;
    }
}
