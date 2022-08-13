<?php

namespace App\Tests\User;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class CheckEmailTest extends ApiTestCase
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

    public function testCheckAccount() {
        $json = [
            "email" => "test@test.fr",
            'verifyEmail' => "00000"
        ];

        $this->client->request('POST', '/api/checkAccount', ['json' => $json]);
        
        
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your account is verified',
        ]);
    }

    public function testBadCheckAccount() {
        $json = [
            "email" => "blablabla@test.fr",
            'verifyEmail' => "00000"
        ];

        $this->client->request('POST', '/api/checkAccount', ['json' => $json]);
        
        
        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your token and email is not correct',
        ]);
    }

    public function testCheckAccountAlreadyVerified() {
        $json = [
            "email" => "test@test.com",
            'verifyEmail' => "11111"
        ];

        $this->client->request('POST', '/api/checkAccount', ['json' => $json]);
        
        
        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your account is already verified',
        ]);
    }
}
