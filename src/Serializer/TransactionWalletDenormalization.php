<?php 

/* A traité quand on aura intégré le current wallet dans l'application */

namespace App\Serializer;

use App\Doctrine\Transaction\TransactionUserOwnedInterface;
use App\Repository\WalletRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class TransactionWalletDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'TransactionWalletDenormalizer';

    public function __construct(private Security $security, private WalletRepository $walletRepository)
    {
        
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        $reflextionClass = new \ReflectionClass($type);
        $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;

        return $reflextionClass->implementsInterface(TransactionUserOwnedInterface::class) && $alreadyCalled == false;
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;
        $obj = $this->denormalizer->denormalize($data, $type, $format, $context); 

        
        $wallets = $this->walletRepository->findBy([
            'owner' => $this->security->getUser()
        ]);
        
        // Simulation d'un wallet courant, sélectionnez par l'user (il faudra le mettre dans le jwt)
        $currentWallet = $wallets[0]; 

        $obj->setWallet($currentWallet);
        
        return $obj;

    }
}