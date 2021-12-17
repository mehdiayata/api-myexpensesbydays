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
    }


    public function testGetWallets()
    {
        $this->client->request('GET', '/api/wallets', $this->header);

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
}