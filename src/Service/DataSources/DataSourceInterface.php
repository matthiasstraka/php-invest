<?php

namespace App\Service\DataSources;

use App\Entity\Asset;

interface DataSourceInterface
{
    /**
     * Fetch prices for an asset between start and end-date
     * 
     * @return List of newly created AssetPrice objects
     */
    public function getPrices(Asset $asset, \DateTimeInterface $startdate, \DateTimeInterface $enddate) : array;

    /**
     * Returns true if the source is available
     */
    public function isAvailable() : bool;
}
