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

    public function testGetWallet()
    {
        $this->client->request('GET', '/api/transactions/2', ['headers' => $this->header]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains(
            [
                "@context" => "/api/contexts/Transaction",
                "@id" => "/api/transactions/2",
                "@type" => "Transaction",
                "id" => 2,
                "amount" => "1573.64",
                "createdAt" => $this->dateFormatService->formatDate('2021-12-15 09:48:49'),
                "editAt" => null
            ]
        );
    }

    // public function testPostWallet()
    // {
    //     $json = [
    //         "amount" => "355.55",
    //         "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
    //     ];


    //     $this->client->request('POST', '/api/wallets', ['headers' => $this->header, 'json' => $json]);


    //     $this->assertResponseStatusCodeSame(201);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    //     $this->assertJsonEquals([
    //         '@context' => '/api/contexts/Wallet',
    //         '@id' => "/api/wallets/3",
    //         '@type' => 'Wallet',
    //         'id' => 3,
    //         "amount" => "355.55",
    //         "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
    //         "editAt" => null
    //     ]);

    //     $this->assertMatchesResourceItemJsonSchema(Wallet::class);

    // }

    // public function testPutWallet()
    // {
    //     $json = [
    //         "amount" => "100.55",
    //         "editAt" => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
    //     ];

    //     $this->client->request('PUT', '/api/wallets/2', ['headers' => $this->header, 'json' => $json]);


    //     $this->assertResponseStatusCodeSame(200);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

    //     $this->assertJsonEquals([
    //         '@context' => '/api/contexts/Wallet',
    //         '@id' => "/api/wallets/2",
    //         '@type' => 'Wallet',
    //         'id' => 2,
    //         "amount" => "100.55",
    //         "createdAt" => $this->dateFormatService->formatDate('2021-12-16 20:45:46'),
    //         'editAt' => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
    //     ]);

    //     $this->assertMatchesResourceItemJsonSchema(Wallet::class);
    // }

    // public function testDeleteWallet()
    // {

    //     $this->client->request('DELETE', '/api/wallets/2', ['headers' => $this->header]);

    //     $this->assertResponseStatusCodeSame(204);

    //     $this->assertNull(
    //         static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['id' => '2'])
    //     );
    // }

    // public function testGetWalletTransactions()
    // {

    //     $this->client->request('GET', '/api/wallets/1/transactions', ['headers' => $this->header]);

    //     $this->assertResponseStatusCodeSame(200);

    //     $this->assertJsonContains( [
    //         "@context" => "/api/contexts/Wallet", 
    //         "@id" => "/api/wallets/1", 
    //         "@type" => "Wallet", 
    //         "amount" => "583644.70", 
    //         "transactions" => [
    //               [
    //                  "@id" => "/api/transactions/1", 
    //                  "@type" => "Transaction", 
    //                  "id" => 1, 
    //                  "amount" => "1257.58", 
    //                  'createdAt' => '2021-12-15T15:02:13+00:00', 
    //                  "editAt" => null 
    //               ], 
    //               [
    //                     "@id" => "/api/transactions/5", 
    //                     "@type" => "Transaction", 
    //                     "id" => 5, 
    //                     "amount" => "1251.91", 
    //                     'createdAt' => '2021-12-16T02:10:38+00:00',
    //                     "editAt" => null 
    //                  ], 
    //               [
    //                        "@id" => "/api/transactions/7", 
    //                        "@type" => "Transaction", 
    //                        "id" => 7, 
    //                        "amount" => "2392.14", 
    //                        'createdAt' => '2021-12-15T10:50:32+00:00',
    //                        "editAt" => null 
    //                     ], 
    //               [
    //                           "@id" => "/api/transactions/8", 
    //                           "@type" => "Transaction", 
    //                           "id" => 8, 
    //                           "amount" => "2147.91", 
    //                           'createdAt' => '2021-12-16T22:28:36+00:00',
    //                           "editAt" => null 
    //                        ] 
    //            ], 
    //         'createdAt' => '2021-12-15T00:20:24+00:00',
    //         "editAt" => null 
    //      ]);
    // }
}
