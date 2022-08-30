<?php

namespace App\Service;

class CalculService
{
    public function calculAuthorizedExpenses($income, $coast, $saving, $savingReal)
    {
        $authorizedExpense = (($income - $coast - $saving) + $savingReal) / ($this->nbDaysLeft() + 1);

        return number_format((float)$authorizedExpense, 2, '.', '');
    }

    // Calcul le savingReal après un edit d'une transaction
    public function calculNewSavingRealPut($savingReal, $oldTransactionAmount, $newTransactionAmount)
    {
        return $savingReal + ($newTransactionAmount - $oldTransactionAmount);
    }

    public function calculeNewSavingRealDelete($savingReal, $oldTransactionAmount)
    {
        return $savingReal - $oldTransactionAmount;
    }

    public function nbDaysLeft()
    {

        return intval(date('t')) - intval(date('d'));
    }
}
