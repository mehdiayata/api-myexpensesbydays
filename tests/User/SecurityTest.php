<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Entity\Wallet;
use App\Tests\LoginTestClass;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class SecurityTest extends ApiTestCase
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

    public function testLogin()
    {
        $client = static::createClient();

        $header = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'azerty13'
            ],
        ];

        $response = $client->request('POST', '/api/login', $header);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
    }


    public function testRegistration()
    {
        $client = static::createClient();

        $json = [
            'json' => [
                'email' => 'test2@test.fr',
                'password' =>  'azerty13'
            ]
        ];

        $client->request('POST', 'api/registration', $json);

        
        // Récupère le nombre d'User
        $nbUser =  count(static::getContainer()->get('doctrine')->getRepository(User::class)->findAll());

        $this->assertResponseIsSuccessful();

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/".$nbUser,
            "@type" => "User",
            "id" => $nbUser,
            "email" => "test2@test.fr",
            "roles" => [
                "ROLE_USER"
            ],
            "firstUse" => true
        ]);

        $this->assertMatchesResourceItemJsonSchema(User::class);


        // Test email
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();

        $this->assertEmailHtmlBodyContains($email, 'Click here to activate your account');

        $this->assertResponseStatusCodeSame(201);

        // Test Login
        $header = [
            'json' => [
                'username' => 'test2@test.fr',
                'password' => 'azerty13'
            ],
        ];

        $client->disableReboot();

        $response = $client->request('POST', '/api/login', $header);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // Test si le wallet a bien été crée
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test2@test.fr']);
        $walletUser = static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['owner' => $user]);

        // Test les data du wallet
        $this->assertEquals(0, $walletUser->getSaving());
        $this->assertEquals(0, $walletUser->getSavingReal());
        $this->assertEquals(0, $walletUser->getAmount());
        $this->assertEquals(true, $walletUser->getMain());
    }

    public function testCheckAccount()
    {
        $client = static::createClient();

        $json = [
            "email" => "testAccount@test.fr",
            'verifyEmail' => "11111"
        ];

        $client->request('POST', '/api/checkAccount', ['json' => $json]);


        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your account is verified',
        ]);
    }

    public function testCheckAccountAlreadyVerified()
    {
        $client = static::createClient();
        $json = [
            "email" => "test@test.fr",
            'verifyEmail' => "22222"
        ];

        $client->request('POST', '/api/checkAccount', ['json' => $json]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your account is already verified',
        ]);
    }

    public function testBadCheckAccount() {
        $client = static::createClient();

        $json = [
            "email" => "fakeEmail@test.fr",
            'verifyEmail' => "00000"
        ];

        $client->request('POST', '/api/checkAccount', ['json' => $json]);
        
        
        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'data' => 'Your token and email is not correct',
        ]);
    }

    public function testSendMailForgotAccount()
    {
        $client = static::createClient();
        $json = [
            "email" => "test@test.fr",
        ];

        $client->request('POST', '/api/forgotPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Your email is valid',
        ]);
    }

    public function testBadSendMailForgotAccount()
    {
        $client = static::createClient();

        $json = [
            "email" => "badEmail@test.fr",
        ];

        $client->request('POST', '/api/forgotPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Email send don\'t exist',
        ]);
    }

    public function testResetPassword()
    {
        $client = static::createClient();

        $json = [
            "email" => "test@test.fr",
            "password" => "newPassword01",
            "resetPassword" => "00000"
        ];

        $client->request('POST', '/api/resetPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Your password is edited',
        ]);

         // Login avec nouveau mot de passe 
         $header = [
            'json' => [
                'username' => 'test@test.fr',
                'password' => 'newPassword01'
            ],
        ];
        
        $client->disableReboot();

        $response = $client->request('POST', '/api/login', $header);
        
        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
    }

    public function testBadResetPassword()
    {
        $client = static::createClient();

        $json = [
            "email" => "test@test.com",
            "password" => "newPassword13",
            "resetPassword" => "11111"
        ];

        $client->request('POST', '/api/resetPassword', ['json' => $json]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json');

        $this->assertJsonContains([
            'message' => 'Impossible edit your password',
        ]);
    }
}
