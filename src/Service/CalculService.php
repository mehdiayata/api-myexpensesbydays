<?php

namespace App\Service;

class CalculService
{
    public function calculAuthorizedExpenses($income, $coast, $saving, $savingReal)
    {
        $authorizedExpense = ($income - $coast - $saving + $savingReal) / ($this->nbDaysLeft() + 1);
        $authorizedExpense = number_format((float)$authorizedExpense, 2, '.', '');
       

        return $authorizedExpense;
    }

    // Calcul le savingReal après un post d'une transaction
    public function calculNewSavingRealPost($savingReal, $newTransactionAmount)
    {
        return number_format((float)$savingReal + $newTransactionAmount, 2, '.', '');
    }


    // Calcul le savingReal après un edit d'une transaction
    public function calculNewSavingRealPut($savingReal, $oldTransactionAmount, $newTransactionAmount)
    {

        return number_format((float)$savingReal + $newTransactionAmount - $oldTransactionAmount, 2, '.', '');
    }

    public function calculNewSavingRealDelete($savingReal, $oldTransactionAmount)
    {
        return number_format((float)$savingReal - $oldTransactionAmount, 2, '.', '');
    }

    public function nbDaysLeft()
    {

        return intval(date('t')) - intval(date('d'));
    }
}
