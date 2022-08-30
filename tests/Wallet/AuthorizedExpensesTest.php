<?php

namespace App\Tests\Wallet;

use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\DateFormatService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Budget;
use App\Service\CalculService;

class AuthorizedExpensesTest extends ApiTestCase
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
    private $budgetRepository;
    private $calculService;

    // Méthode appelé avant chaque test
    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->dateFormatService = new DateFormatService();

        $response = $this->client->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'testAE@test.fr',
                'password' => 'azerty13'
            ],
        ]);

        $this->header = [
            'Authorization' => 'Bearer ' . $response->toArray()['token'],
            'Content-Type' => 'application/json',
        ];

        $this->walletsDB = static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(5);
        $this->budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
        $this->calculService = new CalculService();
    }

    public function testPostTransaction()
    {
        $json = [
            "amount" =>  "50",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "wallet" => "api/wallets/5"
        ];

        $this->client->request('POST', '/api/transactions', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');


        $wallet = $this->client->request('GET', '/api/wallets/5', ['headers' => $this->header, 'json' => $json]);

        $coast = $this->budgetRepository->findSumBudgetByWallet(5, 1);
        $income = $this->budgetRepository->findSumBudgetByWallet(5, 0);
        $wallet = json_decode($wallet->getContent(), true);

        $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Wallet",
            '@id' => '/api/wallets/5',
            '@type' => 'Wallet',
            'id' => 5,
            'amount' => '1050.00',
            'createdAt' => '2021-12-15T22:24:02+00:00',
            "editAt" => null,
            "main" => true,
            "saving" => "100.00",
            "savingReal" => "50.00",
            "authorizedExpenses" => "3537.94",
        ]);
    }
}
