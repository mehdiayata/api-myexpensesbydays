<?php 

namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ResetPasswordController extends AbstractController 
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private UserRepository $userRepository, private EntityManagerInterface $em) {

    }

    public function __invoke(User $data) {
        $userExist = 0;
        
        $userEditPassword = null;

        $users = $this->userRepository->findAll();
        
        foreach ($users as $user) {
            if ($user->getEmail() == $data->getEmail()) {
                $userExist = 1;
                $userEditPassword = $user;
            }
        }

        if($userExist == 1 && $userEditPassword->getResetPassword() == $data->getResetPassword()) {
            $userEditPassword->setPassword($this->passwordHasher->hashPassword($userEditPassword, $data->getPassword()));
            $this->em->flush();
            return  new Response(
                json_encode([
                    'message' => 'Your password is edited'
                ]),
                Response::HTTP_OK,
                ['content-type', 'application/ld+json; charset=utf-8']
            );
        } else {
            return  new Response(
                json_encode([
                    'message' => 'Impossible edit your password'
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type', 'application/ld+json; charset=utf-8']
            );
        }        
    }
}