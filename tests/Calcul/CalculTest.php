<?php

namespace App\Tests\Calcul;

use PHPUnit\Framework\TestCase;
use App\Service\CalculService;

class BudgetTest extends TestCase
{
    public function testCalculAuthorizedExpenses() {
        $calcul = new CalculService;

        $daysLeft = intval(date('t')) - intval(date('d'));
        
        $result = $calcul->calculAuthorizedExpenses(2000, 1000, 500, -50);


        $this->assertEquals(number_format((float)((2000 - 1000 - 500) + -50) / $daysLeft, 2, '.', ''), $result);

    }
   
}
