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
        $aday = new \DateInterval('P1D');
        $last_price = $ap_repo->latestPrice($asset);
        $end_day = (new \DateTime('NOW'))->sub($aday);
        if ($last_price)
        {
            $start_day = $last_price->getDate()->add($aday);
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
        $prices = Marketwatch::getPrices($symbol, $start_day, $end_day);

        $num_prices = count($prices);
        if ($num_prices == 0)
        {
            $io->warning("No prices fetched");
        }
        else
        {
            //var_dump($prices);
            foreach ($prices as $price)
            {
                $ap = new AssetPrice();
                $ap->setAsset($asset);
                $ap->setDate($price['Date']);
                $ap->setOHLC($price['Open'], $price['High'], $price['Low'], $price['Close']);
                $ap->setVolume($price['Volume']);
                $this->entityManager->persist($ap);
            }
            $this->entityManager->flush();
            $io->success("Added $num_prices daily prices");
        }

        return Command::SUCCESS;
    }
}
