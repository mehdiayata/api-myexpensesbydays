<?php

namespace App\Tests\Budget;

use App\Entity\Budget;
use App\Tests\LoginTestClass;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class BudgetTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    // Méthode appelé avant chaque test
    protected function setUp(): void
    {

        $this->client = static::createClient();
        $loginTestClass = new LoginTestClass();
        $token = $loginTestClass->getToken($this->client);

        $this->header = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
    }

    public function testPostIncome()
    {

        $json = [
            "amount" => "100.00",
            "wallet" => "api/wallets/1",
            "dueDate" => ['01', '25', '18'],
            "coast" => false
        ];


        // Récupère le nombre d'enregistrement
        $nbBudget =  count(static::getContainer()->get('doctrine')->getRepository(Budget::class)->findAll());


        $this->client->request('POST', '/api/budgets', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Tester si le retour Json est correct
        $this->assertJsonEquals([
            '@context' => '/api/contexts/Budget',
            '@id' => "/api/budgets/" . $nbBudget + 1,
            '@type' => 'Budget',
            'id' => $nbBudget + 1,
            'wallet' => '/api/wallets/1',
            "amount" => "100.00",
            "dueDate" => ['01', '25', '18'],
            "coast" => false
        ]);

        $this->assertMatchesResourceItemJsonSchema(Budget::class);
    }


    public function testPostCoast()
    {

        $json = [
            "amount" => "250.00",
            "wallet" => "api/wallets/1",
            "dueDate" => ['01', '25', '18'],
            "coast" => true
        ];


        // Récupère le nombre d'enregistrement
        $nbBudget =  count(static::getContainer()->get('doctrine')->getRepository(Budget::class)->findAll());

        $this->client->request('POST', '/api/budgets', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Tester si le retour Json est correct
        $this->assertJsonEquals([
            '@context' => '/api/contexts/Budget',
            '@id' => "/api/budgets/" . $nbBudget + 1,
            '@type' => 'Budget',
            'id' => $nbBudget + 1,
            'wallet' => '/api/wallets/1',
            "amount" => "250.00",
            "dueDate" => ['01', '25', '18'],
            "coast" => true
        ]);

        $this->assertMatchesResourceItemJsonSchema(Budget::class);
    }

    public function testPutCoast()
    {
        $json = [
            "amount" => "150.00",
            "dueDate" => ['1', '2', '3'],
            "coast" => true
        ];

        $test = $this->client->request('PUT', '/api/budgets/1', ['headers' => $this->header, 'json' => $json]);


        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Budget',
            '@id' => "/api/budgets/1",
            '@type' => 'Budget',
            'id' => 1,
            'wallet' => '/api/wallets/2',
            "amount" => "150.00",
            "dueDate" => ['1', '2', '3'],
            "coast" => true
        ]);


        $this->assertMatchesResourceItemJsonSchema(Budget::class);
    }
}
