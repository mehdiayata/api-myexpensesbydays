<?php

namespace App\Tests\Wallet;

use DateTime;
use App\Entity\Wallet;
use App\Tests\LoginTestClass;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Service\DateFormatService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class WalletTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $header;
    private $client;
    private $dateFormatService;
    private $header2;

    protected function setUp(): void
    {
        $this->dateFormatService = new DateFormatService();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $this->client = static::createClient();

        $loginTestClass = new LoginTestClass($this->entityManager);

        $token = $loginTestClass->getToken($this->client);

        $this->header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ];

        $this->header2 = [            
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
    }


    public function testGetWallets()
    {
        $test = $this->client->request('GET', '/api/wallets', $this->header);
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

    public function testGetWallet() {
        $this->client->request('GET', '/api/wallets/2', $this->header);

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
             ]); 
     }

    public function testPostWallet() {
        $json = [
            "amount" => "355.55", 
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46')
            ];


        $this->client->request('POST', '/api/wallets', ['headers' => $this->header2, 'json' => $json]);


        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/Wallet',
            '@id' => "/api/wallets/3",
            '@type' => 'Wallet',
            'id' => 3,
            "amount" => "355.55",
            "createdAt" => $this->dateFormatService->formatDate('2021-12-18 20:45:46'),
            "editAt" => null
        ]);

        $this->assertMatchesResourceItemJsonSchema(Wallet::class);

        // Vérifier que les data sont bien présent
    }

    public function testPutWallet() {
        $json = [
            "amount" => "100.55", 
            "editAt" => $this->dateFormatService->formatDate('2023-01-15 01:02:46')
            ];

        $this->client->request('PUT', '/api/wallets/2', ['headers' => $this->header2, 'json' => $json]);


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

    public function testDeleteWallet() {
        
        $this->client->request('DELETE', '/api/wallets/2', ['headers' => $this->header2]);

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Wallet::class)->findOneBy(['id' => '2'])
        );
    
    }

    
}
