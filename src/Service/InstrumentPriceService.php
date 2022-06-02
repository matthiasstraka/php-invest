<?php

namespace App\Service;

use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentPrice;
use App\Entity\InstrumentTerms;
use App\Service\CurrencyConversionService;
use Doctrine\ORM\EntityManagerInterface;

class InstrumentPriceService
{
    private $entityManager;
    private $fx_xchg;

    public function __construct(EntityManagerInterface $entityManager, CurrencyConversionService $fx_xchg)
    {
        $this->entityManager = $entityManager;
        $this->fx_xchg = $fx_xchg;
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

        $fx_factor = $this->fx_xchg->latestConversion($instrument->getUnderlying()->getCurrency(), $instrument->getCurrency());
        if ($fx_factor == null)
        {
            return null;
        }

        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                $ip->setOHLC(
                    bcmul($fx_factor, $asset_price->getOpen(), 4),
                    bcmul($fx_factor, $asset_price->getHigh(), 4),
                    bcmul($fx_factor, $asset_price->getLow(), 4),
                    bcmul($fx_factor, $asset_price->getClose(), 4));                
                break;
            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return null;
                $factor = $terms->getRatio() * $instrument->getDirection();
                $factor = $factor * $fx_factor;

                $strike = $terms->getStrike();

                $ip->setOHLC(
                    bcmul($factor, $asset_price->getOpen()  - $strike, 4),
                    bcmul($factor, $asset_price->getHigh()  - $strike, 4),
                    bcmul($factor, $asset_price->getLow()   - $strike, 4),
                    bcmul($factor, $asset_price->getClose() - $strike, 4)
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
