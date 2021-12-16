<?php

namespace App\Tests\User;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class LoginTest extends ApiTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }



    use RefreshDatabaseTrait;


    public function testToken()
    {

        $client = static::createClient();

        // Création d'un nouvelle utilisateur
        $this->createUser($client);

        $response = $client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test3@test.fr',
                'password' => 'azerty13'
            ],
        ]);


        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
    }
    

    public function createUser($client) {

        // Création d'un nouvelle utilisateur
        $user = new User();
        $user->setEmail('test3@test.fr');
        $user->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($user, 'azerty13'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
