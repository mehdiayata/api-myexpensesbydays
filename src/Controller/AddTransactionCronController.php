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
        $key = "hJ,[4v!Ts,Z569q/SM4se6A]";

        if (isset($_GET['key']) && $key == $_GET['key']) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:cron:addTransaction'
            ]);
            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $application->run($input, $output);

            // return the output, don't use if you used NullOutput()
            $content = $output->fetch();

            // return new Response(""), if you used NullOutput()
            return new Response($content);
        } else {
            throw $this->createNotFoundException('404 Not found');
        }
    }
}
