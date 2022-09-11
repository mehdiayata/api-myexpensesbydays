<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForgotPasswordMailController extends AbstractController
{
    public function __construct(private UserRepository $userRepository, 
    private MailerInterface $mailer, private EntityManagerInterface $em)
    {
    }

    public function __invoke(User $data, Request $request)
    {
        $userExist = 0;
        $users = $this->userRepository->findAll();
        $userEditPassword = null;

        foreach ($users as $user) {
            if ($user->getEmail() == $data->getEmail()) {
                $userExist = 1;
                $userEditPassword = $user;
            }
        }

        if ($userExist == 1) {
            $randomKey = $this->generateRandomKey();

            $userEditPassword->setResetPassword($randomKey);

            $this->em->flush();


            // Send email
            $this->sendEmail($data->getEmail(), $randomKey);

            return  new Response(
                json_encode([
                    'message' => 'Your email is valid'
                ]),
                Response::HTTP_OK,
                ['content-type', 'application/ld+json; charset=utf-8']
            );
        } else {
            return  new Response(
                json_encode([
                    'message' => 'Email send don\'t exist'
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type', 'application/ld+json; charset=utf-8']
            );
        }
    }

    public function generateRandomKey()
    {
        $length = 32;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = null;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $randomString = base64_encode($randomString);

        return $randomString;
    }


    public function sendEmail($adressEmail, $randomKey)
    {

        $email = (new TemplatedEmail())
            ->from('contact@ayatadev.com')
            ->to($adressEmail)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('myExpensesByDays - Forgot your email')
            ->htmlTemplate('emails/forgotPassword.html.twig')
            ->context([
                'adressEmail' => $adressEmail,
                'randomKey' => $randomKey,
                'url' => $this->getParameter('mail.url').'/#/resetPassword?key=' . $randomKey . '&email=' . $adressEmail
            ]);

        $this->mailer->send($email);
    }
}
