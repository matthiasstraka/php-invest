<?php

namespace App\Command;

use App\Services\FetchPrices;
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
            ->addArgument('symbol', InputArgument::REQUIRED, 'Only fetch a specific symbol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $symbol = $input->getArgument('symbol');

        $a_repo = $this->entityManager->getRepository(Asset::class);
        $asset = $a_repo->findOneBySymbol($symbol);
        if (!$asset)
        {
            $io->error("Symbol $symbol not found");
            return Command::FAILURE;
        }
        else
        {
            $io->note("Symbol $symbol matches asset $asset");
        }

        $ap_repo = $this->entityManager->getRepository(AssetPrice::class);
        $last_price = $ap_repo->latestPrice($asset);

        $end_day = new \DateTime('yesterday');
        if ($last_price)
        {
            $start_day = $last_price->getDate()->add(new \DateInterval('P1D'));
        }
        else
        {
            // get one year worth of data
            $start_day = (new \DateTime('NOW'))->sub(new \DateInterval('P1Y'));
        }

        if ($start_day > $end_day)
        {
            $io->success("Prices are already up to date");
            return Command::SUCCESS;
        }

        $io->note("Fetching prices from {$start_day->format('Y-m-d')} to {$end_day->format('Y-m-d')}");

        $service = new FetchPrices($this->entityManager);
        $num_prices = $service->updatePrices($asset, $start_day, $end_day);

        if ($num_prices == 0)
        {
            $io->success("No prices fetched");
        }
        else
        {
            $io->success("Added $num_prices daily prices");
        }

        return Command::SUCCESS;
    }
}
