<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserPutController extends AbstractController 
{
    public function __construct(private EntityManagerInterface $em, private UserRepository $walletRepository, private UserPasswordHasherInterface $passwordHasher)
    {
        
    }
    public function __invoke(User $data, Request $request): User
    {
        $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));

        return $data;
    }
}