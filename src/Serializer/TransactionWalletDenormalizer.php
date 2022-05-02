<?php

/* A traité quand on aura intégré le current wallet dans l'application */

namespace App\Serializer;

use App\Doctrine\Transaction\TransactionUserOwnedInterface;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class TransactionWalletDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'TransactionWalletDenormalizer';

    public function __construct(private Security $security, private WalletRepository $walletRepository, private EntityManagerInterface $em, private TransactionRepository $transactionRepository)
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
        if (isset($context['item_operation_name']) && $context['item_operation_name'] == 'put') {

            $amountOldTransaction = $context['object_to_populate']->getAmount();
        } else {
            $amountOldTransaction = null;
        }

        $context[self::ALREADY_CALLED_DENORMALIZER] = true;
        $obj = $this->denormalizer->denormalize($data, $type, $format, $context);

        $this->editAmountWallet($obj, $context, $amountOldTransaction);

        return $obj;
    }

    public function editAmountWallet($obj, $context, $amountOldTransaction)
    {

        if (isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'post') {
            $wallet = $obj->getWallet();
            $result = $wallet->getAmount() + $obj->getAmount();
            $wallet->setAmount($result);
        }

        if (isset($context['item_operation_name']) && $context['item_operation_name'] == 'put') {
            // Récupère la somme de la transaciton à modifier (avant que cette dernière ne soit modifier)
            $wallet = $obj->getWallet();
            $result = $wallet->getAmount() - $amountOldTransaction;

            $result = $result + $obj->getAmount();

            $wallet->setAmount($result);
        }


        $this->em->persist($wallet);
        $this->em->flush();
    }
}
