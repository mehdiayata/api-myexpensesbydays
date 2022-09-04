<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\LoginTestClass;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private $client;
    private $header;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $loginTestClass = new LoginTestClass();

        $token = $loginTestClass->getToken(static::createClient(), 'test@test.fr', 'azerty13');

        $this->header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ];
    }


    public function testGetUser()
    {
        $client = static::createClient();

        $client->request('GET', '/api/users/1', $this->header);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users/1',
            '@type' => 'User',
            'id' => 1,
            'email' => 'test@test.fr',
            'roles' => ['0' => 'ROLE_USER'],
            "firstUse" => true
        ]);
    }
    

    public function testPutPassword()
    {
        $client = static::createClient();
       
        $this->header['json'] = ["password" => "newPassword13"];

        $client->request('PUT', '/api/users/1', $this->header);

        // Test si un update a bien été fait
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/1",
            "@type" => "User",
            "id" => 1,
            "email" => "test@test.fr",
            "roles" => ["ROLE_USER"],
            "firstUse" => true
        ]);

        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Login avec nouveau mot de passe 
        $header = [
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'newPassword13'
            ],
        ];
        
        $client->disableReboot();

        $response = $client->request('POST', '/api/login', $header);
        
        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
    }


    public function testPutFirstUse()
    {
        $client = static::createClient();
        $this->header['json'] = ['firstUse' => false];


        $client->request('PUT', '/api/users/1', $this->header);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/1",
            "@type" => "User",
            "id" => 1,
            "email" => "test@test.fr",
            "roles" => ["ROLE_USER"],
            "firstUse" => false
        ]);

        $this->assertMatchesResourceItemJsonSchema(User::class);
    }


    public function testDeleteUser()
    {

        $client = static::createClient();
        $client->request('DELETE', '/api/users/1', $this->header);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['id' => '1'])
        );
    }
}
