<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\LoginTestClass;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $client;
    private $token;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->client = static::createClient();
        
        $loginTestClass = new LoginTestClass();   
        $this->token = $loginTestClass->getToken($this->client); 
    }


    public function testGetUser()
    {
        // Récupère le token 
        $token = $this->token;

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ];

        $test = $this->client->request('GET', '/api/users/4', $header);

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




    public function testPutPassword()
    {

        // Récupère le token (Login)
        $token = $this->token;

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'password' => 'newPassword'
            ]

        ];

        $this->client->request('PUT', '/api/users/4', $header);

        // Test si un update a bien été fait
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/4",
            "@type" => "User",
            "id" => 4,
            "email" => "test@test.fr",
            "roles" => ["ROLE_USER"]
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);


        // Test si l'on peut se connecté avec le nouveau mot de passe
        $header = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'newPassword'
            ],
        ];

        $response = $this->client->request('POST', '/api/login', $header);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

    }

}
