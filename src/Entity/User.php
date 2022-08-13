<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\Controller\UserPutController;
use App\Controller\CheckEmailController;
use App\Doctrine\DataUserOwnedInterface;
use App\Controller\RegistrationController;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
#[ApiResource(
    normalizationContext: ['groups' => 'read:User'],
    collectionOperations: [
        'registration' => [
            'pagination_enabled' => false,
            'path' => '/registration',
            'method' => 'post',
            'controller' => RegistrationController::class,
            'read' => true,
            'denormalization_context' => ['groups' => 'User:Registration'],
        ], 
        'check_email' => [
            'pagination_enabled' => false,
            'path' => '/checkAccount',
            'method' => 'post',
            'controller' => CheckEmailController::class,
            'read' => true,
            'denormalization_context' => ['groups' => 'User:Check:Email'],
            
        ]
    ],
    itemOperations: [
        'get' => [
            'openapi_context' =>  [
                'security' => [['bearerAuth' => []]]
            ],
        ],
        'put' => [
            'openapi_context' => [
                'security' => [['bearerAuth' => []]]
                ],
            'denormalization_context' => ['groups' => 'put:User'],
            'controller' => UserPutController::class,
            'method' => 'put',
        ],
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, DataUserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:User'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    #[Groups(['read:User', 'User:Registration', 'User:Check:Email'])]
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    #[Groups(['read:User'])]
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    #[Groups(['User:Registration', 'put:User'])]
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Wallet::class, mappedBy="owner", orphanRemoval=true)
     */
    private $wallets;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    #[Groups(['User:Check:Email'])]
    private $verifyEmail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    public function __construct()
    {
        $this->wallets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Wallet[]
     */
    public function getWallets(): Collection
    {
        return $this->wallets;
    }

    public function addWallet(Wallet $wallet): self
    {
        if (!$this->wallets->contains($wallet)) {
            $this->wallets[] = $wallet;
            $wallet->setOwner($this);
        }

        return $this;
    }

    public function removeWallet(Wallet $wallet): self
    {
        if ($this->wallets->removeElement($wallet)) {
            // set the owning side to null (unless already changed)
            if ($wallet->getOwner() === $this) {
                $wallet->setOwner(null);
            }
        }

        return $this;
    }

    public function getVerifyEmail()
    {
        return $this->verifyEmail;
    }

    public function setVerifyEmail($verifyEmail): self
    {
        $this->verifyEmail = $verifyEmail;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
