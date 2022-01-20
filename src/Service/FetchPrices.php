<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Service\DataSources\Marketwatch;
use Doctrine\ORM\EntityManagerInterface;

class FetchPrices
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

        $source = new Marketwatch();
        $prices = $source->getPrices($asset, $startdate, $enddate);
        //var_dump($prices);

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
?>
