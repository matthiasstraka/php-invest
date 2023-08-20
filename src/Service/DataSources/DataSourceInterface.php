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

    /**
     * Returns the full name of the data source
     */
    public function getName() : string;

    /**
     * Returns the upper case prefix of the datasource used in the data source expression (e.g. DS/aapl)
     */
    public function getPrefix() : string;

    /**
     * Returns true if price data can be provided for the given asset
     */
    public function supports(Asset $asset) : bool;
}
