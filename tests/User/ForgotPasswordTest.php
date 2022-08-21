<?php

namespace App\Tests\User;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class ForgotPasswordTest extends ApiTestCase
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

    public function testSendMailForgotAccount()
    {
        $json = [
            "email" => "test@test.fr",
        ];

        $this->client->request('POST', '/api/forgotPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Your email is valid',
        ]);
    }

    public function testBadSendMailForgotAccount()
    {
        $json = [
            "email" => "badEmail@test.fr",
        ];

        $this->client->request('POST', '/api/forgotPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Email send don\'t exist',
        ]);
    }

    public function testResetPassword()
    {
        $json = [
            "email" => "test@test.com",
            "password" => "azerty13",
            "resetPassword" => "00000"
        ];

        $this->client->request('POST', '/api/resetPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Your password is edited',
        ]);
    }

    
    public function testBadResetPassword()
    {
        $json = [
            "email" => "test@test.com",
            "password" => "azerty13",
            "resetPassword" => "11111"
        ];

        $this->client->request('POST', '/api/resetPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Impossible edit your password',
        ]);
    }
}
