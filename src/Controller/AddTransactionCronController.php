<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;

class AddTransactionCronController extends AbstractController
{
    #[Route('/cron/addTransaction', name: 'add_transaction_cron')]
    public function index(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        
        $input = new ArrayInput([
            'command' => 'app:cron:add'
        ]);
               // You can use NullOutput() if you don't need the output
               $output = new BufferedOutput();
               $application->run($input, $output);
       
               // return the output, don't use if you used NullOutput()
               $content = $output->fetch();
       
               // return new Response(""), if you used NullOutput()
               return new Response($content);
    }
}
