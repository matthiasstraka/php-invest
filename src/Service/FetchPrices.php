<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Service\DataSources\Alphavantage;
use App\Service\DataSources\Marketwatch;
use App\Service\DataSources\Onvista;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchPrices
{
    private $entityManager;
    private array $datasources;

    public function __construct(
        EntityManagerInterface $entityManager,
        HttpClientInterface $client)
    {
        $this->entityManager = $entityManager;

        $this->datasources = [
            new Alphavantage($client),
            new Onvista($client),
            //new Marketwatch($client), // Must be last in list because it is the fallback
        ];
    }

    public function updatePrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate = null)
    {
        if ($enddate == null)
        {
            $enddate = new \DateTime("yesterday");
        }

        if ($startdate > $enddate)
        {
            return 0;
        }

        // try to find a datasource that accepts the asset
        $source = null;
        foreach ($this->datasources as $candidate)
        {
            if ($candidate->supports($asset))
            {
                $source = $candidate;
                break;
            }
        }

        if (is_null($source))
        {
            throw new \RuntimeException("Unsupported datasource for asset: " . $asset->getName());
        }

        $prices = $source->getPrices($asset, $startdate, $enddate);

        $num_prices = count($prices);
        if ($num_prices == 0)
        {
            return 0;
        }
        else
        {
            foreach ($prices as $price)
            {
                $this->entityManager->persist($price);
            }
            $this->entityManager->flush();
            return $num_prices;
        }
    }
}
