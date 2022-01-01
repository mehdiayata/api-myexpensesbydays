<?php

namespace App\Tests\Wallet;

use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\DateFormatService;
use App\Repository\WalletRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class WalletTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
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
        $this->client->request('GET', '/api/wallets', ['headers' => $this->header]);
        
        // Si la réponse est OK
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            "@context" => "/api/contexts/Wallet",
            "@id" => "/api/wallets",
            "@type" => "hydra:Collection",
            "hydra:member" => [
                [
                    "@id" => "/api/wallets/1",
                    "@type" => "Wallet",
                    "id" => 1,
                    "amount" => "583644.70",
                    'createdAt' => $this->dateFormatService->formatDate('2021-12-15 00:20:24'),
                    "editAt" => null
                ],
                [
                    "@id" => "/api/wallets/2",
                    "@type" => "Wallet",
                    "id" => 2,
                    "amount" => "723121.24",
                    'createdAt' => $this->dateFormatService->formatDate('2021-12-16 20:45:46'),
                    "editAt" => null
                ]
            ],
            "hydra:totalItems" => 2
        ]);
    }

    public function testGetWallet()
    {
        $this->client->request('GET', '/api/wallets/2', ['headers' => $this->header]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains(
            [
                "@context" => "/api/contexts/Wallet",
                "@id" => "/api/wallets/2",
                "@type" => "Wallet",
                "id" => 2,
                "amount" => "723121.24",
                "createdAt" => $this->dateFormatService->formatDate('2021-12-16 20:45:46'),
                "editAt" => null
            ]
        );
    }

    public function testPostWallet()
    {
        $json = [
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ];



        // Récupère le nombre d'enregistrement
        $nbWallets =  count(static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findAll());

        $this->client->request('POST', '/api/wallets', ['headers' => $this->header, 'json' => $json]);


        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/".$nbWallets+1,
            '@type' => 'Wallet',
            'id' => $nbWallets+1,
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "editAt" => null
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);

    }

    public function testPutWallet()
    {
        $json = [
            "amount" => "100.55",
            "editAt" => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
        ];

        $this->client->request('PUT', '/api/wallets/2', ['headers' => $this->header, 'json' => $json]);


        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/2",
            '@type' => 'Wallet',
            'id' => 2,
            "amount" => "100.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-16 20:45:46'),
            'editAt' => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
        ]);

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
  
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonEquals($wallet->getContent());

    }
}
