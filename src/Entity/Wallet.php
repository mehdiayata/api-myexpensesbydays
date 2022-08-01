<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\UserOwnedInterface;
use App\Repository\WalletRepository;
use App\Controller\GetBudgetByWallet;
use App\Controller\MainWalletController;
use App\Controller\GetTransactionByWallet;
use App\Controller\WalletDeleteController;
use App\Controller\GetMainWalletController;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WalletRepository::class)
 */

#[ApiResource(

    denormalizationContext: ['groups' => 'write:Wallet'],
    normalizationContext: ['groups' => 'read:Wallet'],
    collectionOperations: [
        'get' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ]
        ],
        'post' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
        'wallet_get_main' => [
            'pagination_enabled' => false,
            'path' => '/wallets/main',
            'method' => 'get',
            'read' => true,
            'controller' => GetMainWalletController::class,
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ]
        ],
    ],
    itemOperations: [
        'get' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
        'put' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'denormalization_context' => ['groups' => 'put:Wallet']
        ],
        'delete' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'method' => 'delete',
            'controller' => WalletDeleteController::class,
            'read' => true
        ],
        'wallet_transactions' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'path' => '/wallets/{id}/transactions',
            'method' => 'get',
            'pagination_enabled' => true,
            'read' => true,
            'normalization_context' => ['groups' => 'read:Wallet:Transaction', 'subresource_operation_name' => ''],
            'controller' => GetTransactionByWallet::class
        ],
        'wallet_budget_coast' => [
            'path' => '/wallets/{id}/budgets/coasts',
            'method' => 'get',
            'pagination_enabled' => false,
            'read' => true,
            'normalization_context' => ['groups' => 'read:Wallet:Budget', 'subresource_operation_name' => ''],
            'controller' => GetBudgetByWallet::class,
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
        'wallet_budget_income' => [
            'path' => '/wallets/{id}/budgets/incomes',
            'method' => 'get',
            'pagination_enabled' => false,
            'read' => true,
            'normalization_context' => ['groups' => 'read:Wallet:Budget', 'subresource_operation_name' => ''],
            'controller' => GetBudgetByWallet::class,
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
        'wallet_main' => [
            'pagination_enabled' => false,
            'path' => '/wallets/{id}/main',
            'method' => 'put',
            'read' => true,
            'denormalization_context' => ['groups' => 'put:Wallet:main'],
            'controller' => MainWalletController::class,
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ]
        ],

    ]
)]
class Wallet implements UserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:Wallet'])]
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    #[Groups(['read:Wallet', 'write:Wallet', 'put:Wallet', 'read:Wallet:Transaction', 'read:Wallet:Budget'])]
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="wallets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="wallet", orphanRemoval=true)
     */

    #[Groups(['read:Wallet:Transaction'])]
    private $transactions;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['read:Wallet', 'write:Wallet', 'read:Wallet:Transaction', 'read:Wallet:Budget'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['read:Wallet', 'put:Wallet', 'read:Wallet:Transaction', 'read:Wallet:Transaction'])]
    private $editAt;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['read:Wallet'])]
    private $main = 0;

    /**
     * @ORM\OneToMany(targetEntity=Budget::class, mappedBy="wallet", orphanRemoval=true)
     */
    #[Groups(['read:Wallet:Transaction'])]
    private $budgets;


    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"default" : 0})
     */
    #[Groups(['write:Wallet', 'put:Wallet', 'read:Wallet'])]
    private $saving;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"default" : 0})
     */
    #[Groups(['write:Wallet', 'put:Wallet', 'read:Wallet'])]
    private $savingReal;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->budgets = new ArrayCollection();
    }

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setWallet($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getWallet() === $this) {
                $transaction->setWallet(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEditAt(): ?\DateTimeInterface
    {
        return $this->editAt;
    }

    public function setEditAt(?\DateTimeInterface $editAt): self
    {
        $this->editAt = $editAt;

        return $this;
    }

    public function getMain(): ?bool
    {
        return $this->main;
    }

    public function setMain(bool $main): self
    {
        $this->main = $main;

        return $this;
    }

    /**
     * @return Collection|Budget[]
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudget(Budget $budget): self
    {
        if (!$this->budgets->contains($budget)) {
            $this->budgets[] = $budget;
            $budget->setWallet($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): self
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getWallet() === $this) {
                $budget->setWallet(null);
            }
        }

        return $this;
    }

    public function getSaving(): ?string
    {
        return $this->saving;
    }

    public function setSaving(string $saving): self
    {
        $this->saving = $saving;

        return $this;
    }

    public function getSavingReal(): ?string
    {
        return $this->savingReal;
    }

    public function setSavingReal(string $savingReal): self
    {
        $this->savingReal = $savingReal;

        return $this;
    }
}
