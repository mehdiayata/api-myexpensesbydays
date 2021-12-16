<?php

namespace App\Tests\User;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class RegistrationTest extends ApiTestCase 
{
    use RefreshDatabaseTrait;

    public function testRegistration() {
        $user = new User;

        $json = [
            'email' => "test2@test.fr",
            'password' =>  self::getContainer()->get('security.user_password_hasher')->hashPassword($user, 'test')
        ];

        $response = static::createClient()->request('POST', '/api/registration', ['json' => $json]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users/5',
            '@type' => 'User',
            'id' => '5',
            'email' => 'test2@test.fr',
            'roles' => ['0' => 'ROLE_USER']
        ]);


        $this->assertMatchesResourceItemJsonSchema(User::class);
    }


}