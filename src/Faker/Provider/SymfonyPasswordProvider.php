<?php declare(strict_types=1); # src/App/Faker/Provider/SymfonyPasswordProvider.php

namespace App\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

final class SymfonyPasswordProvider extends Base
{
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    /**
     * {@inheritdoc}
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(Generator $generator, EncoderFactoryInterface $encoderFactory)
    {
        parent::__construct($generator);

        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param string $userClass
     * @param string $plainPassword
     * @param string|null $salt
     *
     * @return string
     */
    public function symfonyPassword(string $userClass, string $plainPassword, string $salt = null): string
    {
        $password = $this->encoderFactory->getEncoder($userClass)->encodePassword($plainPassword, $salt);

        return $this->generator->parse($password);
    }
}