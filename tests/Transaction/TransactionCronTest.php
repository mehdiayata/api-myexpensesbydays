<?php 

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;


class TransactionCronTest extends KernelTestCase
{
    public function testExecute() 
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:cron:addTransaction');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}