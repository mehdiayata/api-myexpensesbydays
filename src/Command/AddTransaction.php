<?php 

namespace App\Command;

use App\Repository\TransactionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddTransaction extends Command
{
    protected static $defaultName = 'app:cron:addTransaction';
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;

        parent::__construct();
    }

    protected function configure() {
        $this->setDescription('Add transaction all days by dueDate of Budget');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int 
    {
        $io = new SymfonyStyle($input, $output);    
        
        $this->transactionRepository->addTransactionByBudget();
        
        return 0;
    }
}