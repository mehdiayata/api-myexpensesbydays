<?php 

namespace App\Serializer;

use App\Doctrine\UserOwnedInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class UserOwnedDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'UserOwnedDenormalizer';

    public function __construct(private Security $security)
    {
        
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        $reflextionClass = new \ReflectionClass($type);
        $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;

        return $reflextionClass->implementsInterface(UserOwnedInterface::class) && $alreadyCalled == false;
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;
        $obj = $this->denormalizer->denormalize($data, $type, $format, $context); 

        $obj->setOwner($this->security->getUser());


        return $obj;


    }
}