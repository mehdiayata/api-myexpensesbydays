<?php

namespace App\Tests\User;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
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

    public function testGetUser()
    {
        $client = static::createClient();

        // Création d'un nouvelle utilisateur
        $this->createUser('test3@test.fr');

        // Récupère le token 
        $token = $this->getToken($client);

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ];

        $client->request('GET', '/api/users/4', $header);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users/4',
            '@type' => 'User',
            'id' => '4',
            'email' => 'test@test.fr',
            'roles' => ['0' => 'ROLE_USER']
        ]);

    }


    public function createUser($username)
    {
        // Création d'un nouvelle utilisateur
        $user = new User();
        $user->setEmail($username);
        $user->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($user, 'azerty13'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function getToken($client)
    {

        $response = $client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'azerty13'
            ],
        ]);



        return $response->toArray()['token'];
    }

    public function testPutPassword() {
        $client = static::createClient();

        // Création d'un nouvelle utilisateur
        $this->createUser('test4@test.fr');

        // Récupère le token 
        $token = $this->getToken($client);

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'password' => '4z3rTy13'
            ]

        ];

        $user = $client->request('PUT', '/api/users/4', $header);
        $user = json_decode($user->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($user);

        $this->assertMatchesResourceItemJsonSchema(User::class);
    }
}
