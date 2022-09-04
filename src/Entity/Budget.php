<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BudgetRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\BudgetDeleteController;
use App\Controller\WalletDeleteController;
use App\Doctrine\Transaction\TransactionUserOwnedInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BudgetRepository::class)
 */
#[ApiResource(
    collectionOperations: [
        'post' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
    ],
    itemOperations: [
        'get',
        'put' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'denormalization_context' => ['groups' => 'put:Budget']
        ],
        'delete' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'pagination_enabled' => false,
            'path' => '/budgets/{id}',
            'method' => 'delete',
            'controller' => BudgetDeleteController::class,
            'read' => true
        ],
    ]
)]
class Budget implements TransactionUserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:Wallet:Budget'])]
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    #[Groups(['read:Wallet:Budget', 'put:Budget'])]
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Wallet::class, inversedBy="budgets")
     */
    private $wallet;

    /**
     * @ORM\Column(type="json")
     */
    #[Groups(['read:Wallet:Budget', 'put:Budget'])]
    private $dueDate = [];

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['read:Wallet:Budget', 'put:Budget'])]
    private $coast;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getDueDate(): ?array
    {
        return $this->dueDate;
    }

    public function setDueDate(array $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCoast(): ?bool
    {
        return $this->coast;
    }

    public function setCoast(bool $coast): self
    {
        $this->coast = $coast;

        return $this;
    }
}
