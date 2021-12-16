<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class RegistrationController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function __invoke(User $data, Request $request): User
    {
        $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));

        return $data;
    }
}
