<?php

namespace App\Tests\Wallet;

use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\DateFormatService;
use App\Repository\WalletRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class MainWalletTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $dateFormatService;
    private $client;
    private $header;
    private $walletRepository;
    private $walletsDB;

    // Méthode appelé avant chaque test
    protected function setUp(): void
    {
        $this->dateFormatService = new DateFormatService();

        $this->client = static::createClient();

        $loginTestClass = new LoginTestClass();
        $token = $loginTestClass->getToken($this->client);

        $this->header = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->walletsDB = static::getContainer()->get('doctrine')->getRepository(Wallet::class);
    }

    public function testGetMainWallet()
    {
        $mainWallet = $this->client->request('GET', '/api/wallets/main', ['headers' => $this->header]);
        $mainWallet = json_decode($mainWallet->getContent(), true);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals(
            $mainWallet
        );
    }

    public function testEditMainWallet() 
    {
        
        $json = [];

        $mainWallet = $this->client->request('PUT', '/api/wallets/1/main', ['headers' => $this->header, 'json' => $json]);
        $mainWallet = json_decode($mainWallet->getContent(), true);
     
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        
        $this->assertJsonEquals($mainWallet);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
        
    }
}
