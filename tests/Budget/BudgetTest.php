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

    public function testPostBudget() {

        $json = [
            "amount" => "100.00",
            "wallet" => "api/wallets/1"
        ];

        
        // Récupère le nombre d'enregistrement
        $nbBudget =  count(static::getContainer()->get('doctrine')->getRepository(Budget::class)->findAll());


        $this->client->request('POST', '/api/budgets', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Tester si le retour Json est correct
        $this->assertJsonEquals([
            '@context' => '/api/contexts/Budget',
            '@id' => "/api/budgets/" . $nbBudget + 1 ,
            '@type' => 'Budget',
            'id' => $nbBudget + 1,
            'wallet' => '/api/wallets/1',
            "amount" => "100.00"
        ]);

        $this->assertMatchesResourceItemJsonSchema(Budget::class);
   
    }

    

}
