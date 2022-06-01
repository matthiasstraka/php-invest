<?php

namespace App\Service;

use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentPrice;
use App\Entity\InstrumentTerms;
use Doctrine\ORM\EntityManagerInterface;

class InstrumentPriceService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function latestPrice(Instrument $instrument, ?InstrumentTerms $terms = null): ?InstrumentPrice
    {
        $ap = $this->entityManager->getRepository(AssetPrice::class);
        $asset_price = $ap->latestPrice($instrument->getUnderlying());
        if ($asset_price == null)
            return null;

        if ($terms == null && $instrument->hasTerms())
        {
            $it = $this->entityManager->getRepository(InstrumentTerms::class);
            $terms = $it->latestTerms($instrument);
        }
        
        $ip = new InstrumentPrice();
        $ip->setInstrument($instrument);
        $ip->setDate($asset_price->getDate());

        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                if ($instrument->getCurrency() != $instrument->getUnderlying()->getCurrency())
                {
                    // Asset and Instrument must have the same currency for this EUSIPA class
                    return null;
                }
                $ip->setOHLC($asset_price->getOpen(), $asset_price->getHigh(), $asset_price->getLow(), $asset_price->getClose());                
                break;
            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return null;
                $factor = $terms->getRatio() * $instrument->getDirection();
                if ($instrument->getCurrency() != $instrument->getUnderlying()->getCurrency())
                {
                    // TODO: factor in currency conversion in this factor, needs currency conversion service
                    return null;
                }
                $strike = $terms->getStrike();

                $ip->setOHLC(
                    $factor * ($asset_price->getOpen() - $strike),
                    $factor * ($asset_price->getHigh() - $strike),
                    $factor * ($asset_price->getLow() - $strike),
                    $factor * ($asset_price->getClose() - $strike)
                );
                break;
            default:
                return null; // not supported
        }
        return $ip;
    }
/*
    public function mostRecentPrices(Instrument $instrument, \DateTimeInterface $from_date)
    {
        TODO
    }
*/
}
