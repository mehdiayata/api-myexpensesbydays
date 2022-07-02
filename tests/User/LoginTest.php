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
    private $client;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->client = static::createClient();
    }

    use RefreshDatabaseTrait;

    public function testToken()
    {
        $header = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'azerty13'
            ],
        ];

        $response = $this->client->request('POST', '/api/login', $header);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
    }
}
