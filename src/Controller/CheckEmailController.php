<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[AsController]
class CheckEmailController extends AbstractController
{

    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em)
    {
    }

    public function __invoke(User $data, Request $request)
    {


        $response = new Response(
            json_encode([
                'data' => 'Your account is verified'
            ]),
            Response::HTTP_OK,
            ['content-type', 'application/ld+json; charset=utf-8']
        );

        $user = $this->userRepository->findOneBy([
            'email' => $data->getEmail(),
            'verifyEmail' => $data->getVerifyEmail()
        ]);

        // Si l'user a été trouvé
        if ($user) {
            if ($user->getIsVerified() === false) {
                $user->setIsVerified(true);

                $this->em->persist($user);
                $this->em->flush();


            } else {
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                $response->setContent(json_encode([
                    'data' => 'Your account is already verified'
                ]));
            }
        } else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(json_encode([
                'data' => 'Your token and email is not correct'
            ]));
        }


        return $response;
    }
}
