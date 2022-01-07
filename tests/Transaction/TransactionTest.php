<?php

namespace App\Tests\Transaction;

use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Service\DateFormatService;

class TransactionTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

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

    public function testGetTransaction()
    {
        $transaction = $this->client->request('GET', '/api/transactions/1', ['headers' => $this->header]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals($transaction->getContent());
    }

    public function testPostTransaction()
    {
        $json = [
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ];

        // Récupère le nombre d'enregistrement
        $nbTransaction =  count(static::getContainer()->get('doctrine')->getRepository(Transaction::class)->findAll());

        $this->client->request('POST', '/api/transactions', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/" . $nbTransaction + 1,
            '@type' => 'Transaction',
            'id' => $nbTransaction + 1,
            'wallet' => '/api/wallets/1',
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "editAt" => null
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);
    }

    public function testPutTransaction()
    {
        $json = [
            "amount" => "210.00",
            "editAt" => $this->dateFormatService->formatDate('2022-01-01 07:00:00')
        ];

        $this->client->request('PUT', '/api/transactions/2', ['headers' => $this->header, 'json' => $json]);


        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');


        $this->assertJsonEquals([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/2",
            '@type' => 'Transaction',
            'id' => 2,
            "amount" => "210.00",
            'wallet' => '/api/wallets/1',
            "createdAt" => $this->dateFormatService->formatDate('2021-12-16 02:10:38'),
            "editAt" => $this->dateFormatService->formatDate('2022-01-01 07:00:00')
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);
    }

    public function testDeleteTransaction()
    {

        $this->client->request('DELETE', '/api/transactions/2', ['headers' => $this->header]);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Transaction::class)->findOneBy(['id' => '2'])
        );
    }

}
