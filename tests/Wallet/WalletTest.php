<?php

namespace App\Tests\Wallet;

use App\Entity\Wallet;
use App\Tests\LoginTestClass;
use App\Service\DateFormatService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class WalletTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private $dateFormatService;
    private $client;
    private $header;
    private $walletRepository;

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
    }

    public function testGetWallets()
    {
        $wallets = $this->client->request('GET', '/api/wallets', ['headers' => $this->header]);
        $wallets = json_decode($wallets->getContent(), true);
        
        // Si la réponse est OK
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains($wallets);
    }

    public function testGetWallet()
    {
        $wallet = $this->client->request('GET', '/api/wallets/2', ['headers' => $this->header]);
        $wallet = json_decode($wallet->getContent(), true);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains($wallet);
    }

    public function testPostWallet()
    {
        $json = [
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "saving" => "0",
            "savingReal" => "0"
        ];

        // Récupère le nombre d'enregistrement
        $nbWallets =  count(static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findAll());

        $wallet = $this->client->request('POST', '/api/wallets', ['headers' => $this->header, 'json' => $json]);
        $wallet = json_decode($wallet->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($wallet);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);

    }

    public function testPutWallet()
    {
        $json = [
            "amount" => "100.55",
            "editAt" => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
        ];

        $wallet = $this->client->request('PUT', '/api/wallets/2', ['headers' => $this->header, 'json' => $json]);
        $wallet = json_decode($wallet->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($wallet);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testDeleteWallet()
    {

        $this->client->request('DELETE', '/api/wallets/2', ['headers' => $this->header]);
        
        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['id' => '2'])
        );
    }

    public function testGetWalletTransactions()
    {
        $wallet = $this->client->request('GET', '/api/wallets/1/transactions', ['headers' => $this->header]);
        $wallet = json_decode($wallet->getContent(), true);
        $this->assertResponseStatusCodeSame(200);


        $this->assertJsonEquals($wallet);

    }

    public function testGetWalletBudget()
    {
        $wallet = $this->client->request('GET', '/api/wallets/1/budgets/coasts', ['headers' => $this->header]);
        $wallet = json_decode($wallet->getContent(), true);
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains([
            "@context"=> "/api/contexts/Wallet",
            "@id"=> "/api/wallets",
            "@type"=> "hydra:Collection",
            "hydra:member"=> [
              [
                "@id" => "/api/budgets/12",
                "@type" => "Budget",
                "id" =>  12,
                "amount" => "895.96",
                "dueDate" => [
                  3,
                  6
                ],
                "coast" => true
            ]
            ],
        ]);
        
        $this->assertJsonEquals($wallet);

    }

    public function testPutSaving() {
        
        $json = [
            "saving" => "500"
        ];
        $wallet = $this->client->request('PUT', '/api/wallets/6', ['headers' => $this->header, 'json' => $json]);
        
        $wallet = json_decode($wallet->getContent(), true);
        

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($wallet);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testPutSavingReal() {
        
        $json = [
            "savingReal" => "10"
        ];

        $wallet = $this->client->request('PUT', '/api/wallets/6', ['headers' => $this->header, 'json' => $json]);
        $wallet = json_decode($wallet->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($wallet);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }
}
