<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TransactionRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\TransactionDeleteController;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Doctrine\Transaction\TransactionUserOwnedInterface;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => 'read:Transaction'],
    denormalizationContext: ['groups' => 'write:Transaction'],
    collectionOperations: [
        // 'get' => [
        //     'openapi_context' =>  [
        //         'security' => [['bearerAuth' => []]]
        //     ],
        // ],
        'post' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        'denormalization_context' => ['groups' => 'post:Transaction']
        ]
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
            'denormalization_context' => ['groups' => 'put:Transaction']
        ],
        'delete' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
            'pagination_enabled' => false,
            'path' => '/transactions/{id}',
            'method' => 'delete',
            'controller' => TransactionDeleteController::class,
            'read' => true
            
        ],
        
    ]
)]
class Transaction implements TransactionUserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:Transaction', 'read:Wallet:Transaction'])]
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    #[Groups(['read:Transaction', 'write:Transaction', 'put:Transaction', 'post:Transaction', 'read:Wallet:Transaction'])]
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Wallet::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['read:Transaction', 'post:Transaction'])]
    private $wallet;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['read:Transaction', 'write:Transaction', 'post:Transaction', 'read:Wallet:Transaction'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['read:Transaction', 'put:Transaction', 'read:Wallet:Transaction'])]
    private $editAt;

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
}
