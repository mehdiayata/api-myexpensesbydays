<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Entity\Budget;
use App\Entity\Wallet;
use App\Tests\LoginTestClass;
use App\Service\CalculService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class WalletTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private $header;
    private $calculService;
    private $budgetRepository;

    protected function setUp(): void
    {
        $loginTestClass = new LoginTestClass();
        $kernel = self::bootKernel();

        $token = $loginTestClass->getToken(static::createClient(), 'test@test.fr', 'azerty13');

        $this->header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ];

        $this->calculService = new CalculService();
        $this->budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
    }


    public function testGetWallets()
    {
        $client = static::createClient();
        $client->request('GET', '/api/wallets', $this->header);

        // Récupère le nombre d'enregistrement
        $nbWallets = count(static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findAll());

        // Si la réponse est OK
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    '@id' => '/api/wallets/1',
                    '@type' => 'Wallet',
                    'id' => 1,
                    'amount' => '1000.00',
                    'createdAt' => '2022-09-03T00:00:00+00:00',
                    'editAt' => null,
                    'main' => true,
                    'saving' => '0.00',
                    'savingReal' => '0.00',
                    'authorizedExpenses' => '0.00',
                ]
            ],
            'hydra:totalItems' => $nbWallets
        ]);
    }

    public function testGetWallet()
    {
        $client = static::createClient();
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/1',
            '@type' => 'Wallet',
            'id' => 1,
            'amount' => '1000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => true,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);


        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testPostWallet()
    {
        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "2000",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "saving" => "0",
            "savingReal" => "0"
        ];

        $client->request('POST', '/api/wallets', $this->header);

        // Récupère le nombre d'enregistrement
        $nbWallets = count(static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findAll());

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/' . $nbWallets,
            '@type' => 'Wallet',
            'id' => $nbWallets,
            'amount' => '2000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => false,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testPutWallet()
    {
        $client = static::createClient();
        $this->header['json'] = [
            "amount" => "3000",
            "editAt" => "2022-09-03T00:00:00+00:00"
        ];

        $client->request('PUT', '/api/wallets/1', $this->header);


        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/1',
            '@type' => 'Wallet',
            'id' => 1,
            'amount' => '3000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => '2022-09-03T00:00:00+00:00',
            'main' => true,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testDeleteWallet()
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/wallets/1', $this->header);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['id' => '1'])
        );


        $client->disableReboot();

        $client->request('GET', '/api/wallets/2', $this->header);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/2',
            '@type' => 'Wallet',
            'id' => 2,
            'amount' => '2000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => false,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testGetWalletTransactions()
    {
        $client = static::createClient();

        $client->request('GET', '/api/wallets/1/transactions', $this->header);

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    '@id' => '/api/transactions/1',
                    '@type' => 'Transaction',
                    'id' => 1,
                    'amount' => '100.00',
                    'createdAt' => '2022-09-03T00:00:00+00:00',
                    'editAt' => null,
                ]
            ],
            'hydra:totalItems' => 2
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }


    public function testGetWalletCoast()
    {
        $client = static::createClient();

        $client->request('GET', '/api/wallets/1/budgets/coasts', $this->header);
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains([
            "@context" => "/api/contexts/Wallet",
            "@id" => "/api/wallets",
            "@type" => "hydra:Collection",
            "hydra:member" => [
                [
                    "@id" => "/api/budgets/3",
                    "@type" => "Budget",
                    "id" =>  3,
                    "amount" => "200.00",
                    "dueDate" => [
                        '10',
                        '18'
                    ],
                    "coast" => true
                ]
            ],
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testGetWalletIncome()
    {
        $client = static::createClient();

        $client->request('GET', '/api/wallets/1/budgets/incomes', $this->header);
        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains([
            "@context" => "/api/contexts/Wallet",
            "@id" => "/api/wallets",
            "@type" => "hydra:Collection",
            "hydra:member" => [
                [
                    "@id" => "/api/budgets/1",
                    "@type" => "Budget",
                    "id" =>  1,
                    "amount" => "500.00",
                    "dueDate" => [
                        '2',
                        '5',
                        '7'
                    ],
                    "coast" => false
                ]
            ],
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testPutSaving()
    {
        $client = static::createClient();

        $this->header['json'] = [
            "saving" => "500"
        ];

        $wallet = $client->request('PUT', '/api/wallets/1', $this->header);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // GET Budget & WalletInfos
        $coast = $this->budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $this->budgetRepository->findSumBudgetByWallet(1, 0);
        $wallet = json_decode($wallet->getContent(), true);


        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);


        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/1',
            '@type' => 'Wallet',
            'id' => 1,
            'amount' => '1000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => true,
            'saving' => '500.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => $authorizedExpenses
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    }

    public function testGetWalletMain()
    {
        $client = static::createClient();
        $client->request('GET', '/api/wallets/main', $this->header);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/1',
            '@type' => 'Wallet',
            'id' => 1,
            'amount' => '1000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => true,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);
    }

    public function testPutWalletMain() {
        $client = static::createClient();
        $this->header['json'] = [];
        $client->request('PUT', '/api/wallets/2/main', $this->header);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/2',
            '@type' => 'Wallet',
            'id' => 2,
            'amount' => '2000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => true,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);

        // Test si le wallet 1 n'est plus "main"
        $client->disableReboot();

        $client->request('GET', '/api/wallets/1', $this->header);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => '/api/wallets/1',
            '@type' => 'Wallet',
            'id' => 1,
            'amount' => '1000.00',
            'createdAt' => '2022-09-03T00:00:00+00:00',
            'editAt' => null,
            'main' => false,
            'saving' => '0.00',
            'savingReal' => '0.00',
            'authorizedExpenses' => '0.00'
        ]);

    }



}
