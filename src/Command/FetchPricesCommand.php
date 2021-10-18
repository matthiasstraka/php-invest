<?php

namespace App\Command;

use App\AppBundle\DataSources\Marketwatch;
use App\Entity\Asset;
use App\Entity\AssetPrice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchPricesCommand extends Command
{
    protected static $defaultName = 'app:fetch-prices';
    protected static $defaultDescription = 'Downloads new price data for all configured assests/instruments';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::OPTIONAL, 'Only fetch a specific symbol')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $symbol = $input->getArgument('symbol');

        if ($symbol) {
            $io->note(sprintf('You selected symbol %s', $symbol));

            $csv = Marketwatch::getPrices($symbol, new \DateTime('2021-10-01'), new \DateTime('2021-10-18'));

            var_dump($csv);
            //$io->writeln($csv);
        }

        //if ($input->getOption('option1')) {}
        
        //$repo = $this->entityManager->getRepository(AssetPrice::class);

        $io->success('The current implementation simply show you the CSV prices. Come back later.');

        return Command::SUCCESS;
    }
}
