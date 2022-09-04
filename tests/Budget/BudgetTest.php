<?php

namespace App\Tests\User;

use App\Entity\Budget;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\CalculService;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class BudgetTest extends ApiTestCase
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
        $this->calculService = new CalculService();
    }

    public function testGetBudget()
    {
        $client = static::createClient();
        $client->request('GET', '/api/budgets/1', $this->header);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Budget",
            "@id" => "/api/budgets/1",
            "@type" => "Budget",
            "id" => 1,
            "amount" => "500.00",
            "wallet" => "/api/wallets/1",
            "dueDate" => ['2', '5', '7'],
            "coast" => false
        ]);


        $this->assertMatchesResourceItemJsonSchema(Budget::class);
    }

    public function testPostBudget()
    {
        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "100",
            "wallet" => "api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ];

        $client->request('POST', '/api/budgets', $this->header);

        // Récupère le nombre d'enregistrement
        $nbBudget =  count(static::getContainer()->get('doctrine')->getRepository(Budget::class)->findAll());

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Budget",
            "@id" => "/api/budgets/" . $nbBudget,
            "@type" => "Budget",
            "id" => $nbBudget,
            "amount" => "100.00",
            "wallet" => "/api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ]);
        
        $this->assertMatchesResourceItemJsonSchema(Budget::class);

        // Test si les valeur du Wallet ont bien changés.
        $client->disableReboot();
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);

        $budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
        
        // GET Budget & WalletInfos
        $coast = $budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $budgetRepository->findSumBudgetByWallet(1, 0);
        $wallet = json_decode($wallet->getContent(), true);


        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);

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
            "savingReal" => "0.00",
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }

    public function testPutBudget()
    {
        // Créer un budget prêt à être modifié
        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "100",
            "wallet" => "api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ];

        $budget = $client->request('POST', '/api/budgets', $this->header);
        $budget = json_decode($budget->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Budget",
            "@id" => "/api/budgets/" . $budget['id'],
            "@type" => "Budget",
            "id" => $budget['id'],
            "amount" => "100.00",
            "wallet" => "/api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ]);
        
        $this->assertMatchesResourceItemJsonSchema(Budget::class);

        // Modifier le budget ci dessus
        $client->disableReboot();

        $this->header['json'] = [
            "amount" => "200",
            "wallet" => "api/wallets/1",
            "dueDate" => ['2', '4', '7'],
            "coast" => true
        ];

        $client->request('PUT', '/api/budgets/'.$budget['id'], $this->header);

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Budget",
            "@id" => "/api/budgets/" . $budget['id'],
            "@type" => "Budget",
            "id" => $budget['id'],
            "amount" => "200.00",
            "wallet" => "/api/wallets/1",
            "dueDate" => ['2', '4', '7'],
            "coast" => true
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(Budget::class);


        // Vérifie si les valeur du Wallet sont correct
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);
        $wallet = json_decode($wallet->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        // GET Budget & WalletInfos
        $budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
        $coast = $budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $budgetRepository->findSumBudgetByWallet(1, 0);
        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);

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
            "savingReal" => "0.00",
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }

    public function testDeleteBudget() {
        // Créer un budget prêt à être modifié
        $client = static::createClient();

        $this->header['json'] = [
            "amount" => "100",
            "wallet" => "api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ];

        $budget = $client->request('POST', '/api/budgets', $this->header);
        $budget = json_decode($budget->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            "@context" => "/api/contexts/Budget",
            "@id" => "/api/budgets/" . $budget['id'],
            "@type" => "Budget",
            "id" => $budget['id'],
            "amount" => "100.00",
            "wallet" => "/api/wallets/1",
            "dueDate" => ['1', '5', '15', '20', '28'],
            "coast" => true
        ]);
        
        $this->assertMatchesResourceItemJsonSchema(Budget::class);

        // Supprime le budget ci dessus
        $client->disableReboot();
        
        $client->request('DELETE', '/api/budgets/'.$budget['id'], $this->header);
        
        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Budget::class)->findOneBy(['id' => $budget['id']])
        );

        // Vérifie si les valeur du Wallet sont correct
        $wallet = $client->request('GET', '/api/wallets/1', $this->header);
        $wallet = json_decode($wallet->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        // GET Budget & WalletInfos
        $budgetRepository = static::getContainer()->get('doctrine')->getRepository(Budget::class);
        $coast = $budgetRepository->findSumBudgetByWallet(1, 1);
        $income = $budgetRepository->findSumBudgetByWallet(1, 0);
    
        $authorizedExpenses = $this->calculService->calculAuthorizedExpenses($income, $coast, $wallet['saving'], $wallet['savingReal']);
    
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
            "savingReal" => "0.00",
            'authorizedExpenses' => $authorizedExpenses
        ]);
    }


}
