<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Entity\Wallet;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class RegistrationTest extends ApiTestCase
{

    use RefreshDatabaseTrait;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegistration()
    {
        $client = $this->registration('test@test.net', 'azerty13');

        $this->assertResponseIsSuccessful(); 
        
        // Test email
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        
        $this->assertEmailHtmlBodyContains($email, 'Click here to activate your account');

        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/7",
            "@type" => "User",
            "id" => 7,
            "email" => "test@test.net",
            "roles" => [
                "ROLE_USER"
            ],
            "firstUse" => true
        ]);


        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testGetWalletUser() {
        
        // Vérifier si le wallet est créer lors de la registration 
        $this->registration('test@test.net', 'azerty13');

        // find wallet by User 
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test@test.net']);
        $walletUser = static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['owner' => $user]);
        
        // Test si les data du wallet
        $this->assertEquals(0, $walletUser->getSaving());
        $this->assertEquals(0, $walletUser->getSavingReal());
        $this->assertEquals(0, $walletUser->getAmount());
        $this->assertEquals(true, $walletUser->getMain());
    }

    public function registration($email, $password){
        $user = new User;

        $json = [
            'email' => $email,
            'password' =>  self::getContainer()->get('security.user_password_hasher')->hashPassword($user, $password)
        ];

        $this->client->request('POST', '/api/registration', ['json' => $json]);

        return $this->client;
    }

}
