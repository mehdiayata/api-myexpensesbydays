<?php 

namespace App\Doctrine\Transaction;

use App\Entity\Wallet;

interface TransactionUserOwnedInterface
{
    public function getWallet(): ?Wallet;
  
}
