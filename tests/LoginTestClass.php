<?php

namespace App\Tests;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class LoginTestClass extends ApiTestCase
{
    private $entityManager;

    public function __construct()
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

    }


    public function getToken($client, $username, $password)
    {
        $response = $client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => $username,
                'password' => $password
            ],
        ]);


        return $response->toArray()['token'];
    }
}
