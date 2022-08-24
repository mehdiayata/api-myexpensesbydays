<?php

namespace App\Service;

use NumberFormatter;

class CalculService
{

    public function calculAuthorizedExpenses($income, $coast, $saving, $savingReal)
    {
        $authorizedExpense = (($income - $coast - $saving) + $savingReal) / $this->nbDaysLeft();

        return number_format((float)$authorizedExpense, 2, '.', '');
        
    }


    public function nbDaysLeft()
    {

        return intval(date('t')) - intval(date('d'));
    }
}
