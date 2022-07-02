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
        $client = $this->registration('test@test.com', 'azerty13');

        $this->assertResponseStatusCodeSame(201);

        $this->assertJsonEquals([
            "@context" => "/api/contexts/User",
            "@id" => "/api/users/5",
            "@type" => "User",
            "id" => 5,
            "email" => "test@test.com",
            "roles" => [
                "ROLE_USER"
            ]
        ]);


        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testGetWalletUser() {
        
        // Vérifier si le wallet est créer lors de la registration 
        $this->registration('test@test.com', 'azerty13');

        // find wallet by User 
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);
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
