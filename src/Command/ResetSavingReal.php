<?php 

namespace App\Command;

use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetSavingReal extends Command
{
    protected static $defaultName = 'app:cron:resetSavingReal';
    private $walletRepository;
    private $em;

    public function __construct(WalletRepository $walletRepository, EntityManagerInterface $em)
    {
        $this->walletRepository = $walletRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure() {
        $this->setDescription('Reset saving real all first mounth');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int 
    {
        $io = new SymfonyStyle($input, $output);    
        
        $wallets = $this->walletRepository->findAll();

        foreach($wallets as $wallet) {
            $wallet->setSavingReal(0);
        }

        $this->em->flush();
        
        return 0;
    }
}