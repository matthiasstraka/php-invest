<?php

namespace App\Command;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:delete-prices')]
class DeletePricesCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Deletes price data for a specific assest/instrument');
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'Symbol of the asset')
        ;
        // TODO: Add date-range, single date, etc.
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $symbol = $input->getArgument('symbol');

        $a_repo = $this->entityManager->getRepository(Asset::class);
        $asset = $a_repo->findOneBySymbol($symbol);
        if (!$asset)
        {
            $io->error("Symbol '$symbol' not found");
            return Command::FAILURE;
        }
        $io->note("Deleting all prices for symbol '$symbol' that matches asset $asset");

        $ap_repo = $this->entityManager->getRepository(AssetPrice::class);
        $num = $ap_repo->deleteAssetPrices($asset);
        $this->entityManager->flush();
        $io->success("Deleted $num prices for symbol '$symbol'");

        return Command::SUCCESS;
    }
}
