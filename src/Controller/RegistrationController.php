<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class RegistrationController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UserRepository $userRepository
    ) {
    }

    public function __invoke(User $data, Request $request)
    {
        // Si l'email est déjà pris
        if ($this->checkEmail($data->getEmail())) {
            $randomKey = $this->generateRandomKey();

            $this->sendEmail($data->getEmail(), $randomKey);

            $data->setVerifyEmail($randomKey);
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));

            $newMainWallet = $this->createMainWallet($data);
            $data->addWallet($newMainWallet);


            return $data;

        } else {

            return new Response(
                json_encode([
                    'message' => 'Your email is already use'
                ]),
                Response::HTTP_BAD_REQUEST,
                ['content-type', 'application/ld+json; charset=utf-8']
            );
        }
    }

    public function checkEmail($email)
    {
        $user = $this->userRepository->findBy([
            'email' => $email
        ]);

        if ($user == null) {
            return true;
        } else {
            return false;
        }
    }

    // Créer un Wallet principal lors de l'inscription
    public function createMainWallet($user)
    {

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

    // Créer chaine encode en base 64 et encrypter (comme le mot de passe)
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
            ->subject('myExpensesByDays - Registration')
            ->htmlTemplate('emails/signup.html.twig')
            ->context([
                'adressEmail' => $adressEmail,
                'randomKey' => $randomKey,
                'url' => 'http://127.0.0.1:3000/#/checkEmail?key=' . $randomKey . '&email=' . $adressEmail
            ]);

        $this->mailer->send($email);
    }
}
