<?php

namespace App\Tests\User;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\CalculService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class TransactionTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private $header;
    private $budgetRepository;
    private $calculService;

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

        $this->budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
        $this->calculService = new CalculService();
    }

    public function testGetTransaction()
    {
        $client = static::createClient();
        $client->request('GET', '/api/transactions/1', $this->header);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Transaction",
            "@id" => "/api/transactions/1",
            "@type" => "Transaction",
            "id" => 1,
            "amount" => "100.00",
            "wallet" => "/api/wallets/1",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => null
        ]);


        $this->assertMatchesResourceItemJsonSchema(Transaction::class);
    }

    public function testPostTransaction()
    {
        $client = static::createClient();
        $this->header['json'] = [
            "amount" => "100",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "wallet" => "api/wallets/1"
        ];

        $client->request('POST', '/api/transactions', $this->header);

        // Récupère le nombre d'enregistrement
        $nbTransaction =  count(static::getContainer()->get('doctrine')->getRepository(Transaction::class)->findAll());

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        
        $this->assertJsonEquals([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/".$nbTransaction,
            '@type' => 'Transaction',
            'id' => $nbTransaction,
            'wallet' => '/api/wallets/1',
            "amount" => "100.00",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => null
        ]);
        
        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        // Test si les valeur du Wallet ont bien changés.
        $client->disableReboot();
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);

        // GET Budget & WalletInfos
        $coast = $this->budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $this->budgetRepository->findSumBudgetByWallet(1, 0);
        $wallet = json_decode($wallet->getContent(), true);


        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);
        $newSavingReal = $this->calculService->calculNewSavingRealPost(0, 100);
        
        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/1",
            '@type' => 'Wallet',
            'id' => 1,
            "amount" => "1100.00",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => null,
            'main' => true,
            'saving' => "0.00",
            "savingReal" => $newSavingReal,
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }

    public function testPutTransaction()
    {
        // Création d'une transaction a éditer

        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "100",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "wallet" => "api/wallets/1"
        ];

        $transaction = $client->request('POST', '/api/transactions', $this->header);
        $transaction = json_decode($transaction->getContent(), true);
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // PUT Transaction
        $client->disableReboot();
           
        $this->header['json'] = [
            "amount" => "200",
            "editAt" => '2022-09-03T00:00:00+00:00'
        ];

        $client->request('PUT', '/api/transactions/'.$transaction['id'], $this->header);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');


        $this->assertJsonContains([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/".$transaction['id'],
            '@type' => 'Transaction',
            'id' => $transaction['id'],
            "amount" => "200.00",
            'wallet' => '/api/wallets/1',
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => '2022-09-03T00:00:00+00:00'
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        // Test si les valeur du Wallet ont bien changés.
        
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);

        // GET Budget & WalletInfos
        $coast = $this->budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $this->budgetRepository->findSumBudgetByWallet(1, 0);
        $wallet = json_decode($wallet->getContent(), true);
        
        $newSavingReal = $this->calculService->calculNewSavingRealPut(100, 100, 200);
        
        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $newSavingReal);
       
        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/1",
            '@type' => 'Wallet',
            'id' => 1,
            "amount" => "1200.00",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => null,
            'main' => true,
            'saving' => "0.00",
            "savingReal" => $newSavingReal,
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }

    public function testDeleteTransaction()
    {
        // Création d'une transaction a supprimer
        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "100",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "wallet" => "api/wallets/1"
        ];

        $transaction = $client->request('POST', '/api/transactions', $this->header);
        
        $transaction = json_decode($transaction->getContent(), true);
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Delete Transaction
        $client->disableReboot();
        
        $client->request('DELETE', '/api/transactions/'.$transaction['id'], $this->header);

        $this->assertResponseStatusCodeSame(204);
        
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Transaction::class)->findOneBy(['id' => $transaction['id']])
        );
       
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);

        $this->assertResponseIsSuccessful();
        
        // GET Budget & WalletInfos
        $coast = $this->budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $this->budgetRepository->findSumBudgetByWallet(1, 0);
        $wallet = json_decode($wallet->getContent(), true);
        
        $newSavingReal = $this->calculService->calculNewSavingRealDelete(100, 100);
       
        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $newSavingReal);
      
        $this->assertJsonContains([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/1",
            '@type' => 'Wallet',
            'id' => 1,
            "amount" => "1000.00",
            "createdAt" => '2022-09-03T00:00:00+00:00',
            "editAt" => null,
            'main' => true,
            'saving' => "0.00",
            "savingReal" => $newSavingReal,
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }

}
