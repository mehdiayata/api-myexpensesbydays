<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class RegistrationController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $em)
    {
    }

    public function __invoke(User $data, Request $request): User
    {
        $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));

        $newMainWallet = $this->createMainWallet($data);
        $data->addWallet($newMainWallet);
        return $data;
    }

    // Créer un Wallet principal lors de l'inscription
    public function createMainWallet($user) {

        $newMainWallet = new Wallet();
        
        $newMainWallet->setAmount(0);
        $newMainWallet->setMain(1);
        $newMainWallet->setOwner($user);
        $newMainWallet->setCreatedAt(new \DateTime('now'));
        $newMainWallet->setSaving(0);
        $newMainWallet->setSavingReal(0);
        
        $this->em->persist($newMainWallet);

        return $newMainWallet;
    }
}
