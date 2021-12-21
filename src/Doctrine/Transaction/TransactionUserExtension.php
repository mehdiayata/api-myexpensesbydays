<?php

namespace App\Doctrine\Transaction;

use ReflectionClass;
use App\Entity\Wallet;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use App\Doctrine\Transaction\TransactionUserOwnedInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use App\Repository\TransactionRepository;

class TransactionUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security, private TransactionRepository $transactionRepository)
    {
        
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?string $operationName = null
    ) {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = [])
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    private function addWhere(string $resourceClass, QueryBuilder $queryBuilder)
    {
        $reflectionClass = new \ReflectionClass($resourceClass);

        // Si la ressource est une classe de Wallet, créer un where pour récupérer seulement l'user
        if ($reflectionClass->implementsInterface(TransactionUserOwnedInterface::class)) {
            
            $alias = $queryBuilder->getRootAliases()[0];
            
            $user = $this->security->getUser();

            if ($user) {
                $queryBuilder->innerJoin("$alias.wallet", 'w')
                ->andWhere("w.owner = :current_user")
                ->setParameter('current_user', $user->getId());
            } else {
                $queryBuilder->andWhere("$alias.id IS NULL");
            }
        }
    }
}
