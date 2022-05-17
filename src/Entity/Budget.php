<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BudgetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BudgetRepository::class)
 */
#[ApiResource(
    collectionOperations: [
        'post' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ]
    ]
)]
class Budget
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Wallet::class, inversedBy="budgets")
     */
    private $wallet;

    /**
     * @ORM\Column(type="json")
     */
    private $dueDate = [];

    /**
     * @ORM\Column(type="boolean")
     */
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
