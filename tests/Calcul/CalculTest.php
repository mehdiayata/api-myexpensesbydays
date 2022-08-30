<?php

namespace App\Tests\Calcul;

use PHPUnit\Framework\TestCase;
use App\Service\CalculService;

class CalculTest extends TestCase
{

    public function testCalculAuthorizedExpenses() {
        $calculService = new CalculService();
        
        $daysLeft = intval(date('t')) - intval(date('d')) + 1;
        
        $result = $calculService->calculAuthorizedExpenses(2000, 1000, 500, -50);

        $this->assertEquals(number_format((float)((2000 - 1000 - 500) + -50) / $daysLeft, 2, '.', ''), $result);
    }
   
       
    public function testcalculNewSavingRealPut() {
        $calculService = new CalculService();

        $result = $calculService->calculNewSavingRealPut(1000, 100, 200);

        $this->assertEquals(1000 + (200 - 100), $result);
    }

}
