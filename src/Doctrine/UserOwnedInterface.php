<?php 

namespace App\Doctrine;

use App\Entity\User;

interface UserOwnedInterface
{
    public function getOwner(): ?User;

    public function setOwner(?User $user): self; 
  
}