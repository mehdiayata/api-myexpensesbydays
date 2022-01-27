<?php

/** Cette série de test permet calculer si la somme du Wallet est exact après chaque transaction */

namespace App\Tests\Transaction;

use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Tests\LoginTestClass;
use App\Service\DateFormatService;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TransactionWalletTest extends ApiTestCase
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

    public function testPostPositiveTransaction()
    {
        $oldAmountWallet =  static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(1)->getAmount();
        $amount = 1000.25;

        $json = [
            "amount" => "$amount",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "wallet" => "api/wallets/1"
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
            "amount" => $amount,
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "editAt" => null
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);


        $wallet = $this->client->request('GET', '/api/wallets/1', ['headers' => $this->header]);

        $newAmountWallet = $serializer->decode($wallet->getContent(), 'json')['amount'];
        $newResult =  $oldAmountWallet + $amount;
        
        $this->assertEquals($newResult, $newAmountWallet);
       
    }

    public function testPostNegativeTransaction() {
        $oldAmountWallet =  static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(1)->getAmount();
        $amount = -183644.70;

        $json = [
            "amount" => "$amount",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "wallet" => "api/wallets/1"
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
            "amount" => $amount,
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "editAt" => null
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $wallet = $this->client->request('GET', '/api/wallets/1', ['headers' => $this->header]);

        $newAmountWallet = $serializer->decode($wallet->getContent(), 'json')['amount'];
        $newResult =  $oldAmountWallet + $amount;

        
        $this->assertEquals($newResult, $newAmountWallet);
    }

    public function testPutPositiveTransaction() {
        $oldAmountWallet =  static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(1)->getAmount();
        $oldAmountTransaction =  static::getContainer()->get('doctrine')->getRepository(Transaction::class)->find(2)->getAmount();
        $amount = 5000.70;

        $json = [
            "amount" => "$amount",
            "editAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ];
        

        $this->client->request('PUT', '/api/transactions/2', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/2",
            '@type' => 'Transaction',
            'id' => 2,
            'wallet' => '/api/wallets/1',
            "amount" => $amount,
            "createdAt" => $this->dateFormatService->formatDate('2021-12-16 02:10:38'),
            "editAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $wallet = $this->client->request('GET', '/api/wallets/1', ['headers' => $this->header]);

        $newAmountWallet = $serializer->decode($wallet->getContent(), 'json')['amount'];
        $newResult =  ($oldAmountWallet - $oldAmountTransaction) + $amount;

        $this->assertEquals($newResult, $newAmountWallet);
    }

    public function testPutNegativeTransaction() {
        $oldAmountWallet =  static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(1)->getAmount();
        $oldAmountTransaction =  static::getContainer()->get('doctrine')->getRepository(Transaction::class)->find(2)->getAmount();
        $amount = -15000.70;

        $json = [
            "amount" => "$amount",
            "editAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ];
        

        $this->client->request('PUT', '/api/transactions/2', ['headers' => $this->header, 'json' => $json]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Transaction',
            '@id' => "/api/transactions/2",
            '@type' => 'Transaction',
            'id' => 2,
            'wallet' => '/api/wallets/1',
            "amount" => $amount,
            "createdAt" => $this->dateFormatService->formatDate('2021-12-16 02:10:38'),
            "editAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
        ]);

        $this->assertMatchesResourceItemJsonSchema(Transaction::class);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $wallet = $this->client->request('GET', '/api/wallets/1', ['headers' => $this->header]);

        $newAmountWallet = $serializer->decode($wallet->getContent(), 'json')['amount'];
        $newResult =  ($oldAmountWallet - $oldAmountTransaction) + $amount;

        
        $this->assertEquals($newResult, $newAmountWallet);
    }


    public function testDeleteTransaction()
    {
        $oldAmountWallet =  static::getContainer()->get('doctrine')->getRepository(Wallet::class)->find(1)->getAmount();
        $amount = static::getContainer()->get('doctrine')->getRepository(Transaction::class)->find(2)->getAmount();
        

        $this->client->request('DELETE', '/api/transactions/2', ['headers' => $this->header]);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Transaction::class)->findOneBy(['id' => '2'])
        );

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $wallet = $this->client->request('GET', '/api/wallets/1', ['headers' => $this->header]);

        $newAmountWallet = $serializer->decode($wallet->getContent(), 'json')['amount'];
        $newResult =  $oldAmountWallet - $amount;
        
        $this->assertEquals($newResult, $newAmountWallet);

    }
}
