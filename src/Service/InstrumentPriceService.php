<?php

namespace App\Service;

use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentPrice;
use App\Entity\InstrumentTerms;
use App\Service\CurrencyConversionService;
use DateTimeInterface;
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

    private static function createScaledPrice(Instrument $instrument, AssetPrice $asset_price, string $scale): InstrumentPrice
    {
        $ip = new InstrumentPrice();
        $ip->setInstrument($instrument);
        $ip->setDate($asset_price->getDate());
        $ip->setOHLC(
            bcmul($scale, $asset_price->getOpen(), 4),
            bcmul($scale, $asset_price->getHigh(), 4),
            bcmul($scale, $asset_price->getLow(), 4),
            bcmul($scale, $asset_price->getClose(), 4));
        return $ip;
    }

    private static function createKnockoutPrice(Instrument $instrument, AssetPrice $asset_price, string $scale, string $strike): InstrumentPrice
    {
        $ip = new InstrumentPrice();
        $ip->setInstrument($instrument);
        $ip->setDate($asset_price->getDate());
        $ip->setOHLC(
            bcmul($scale, bcsub($asset_price->getOpen(), $strike, 6), 4),
            bcmul($scale, bcsub($asset_price->getHigh(), $strike, 6), 4),
            bcmul($scale, bcsub($asset_price->getLow(), $strike, 6), 4),
            bcmul($scale, bcsub($asset_price->getClose(), $strike, 6), 4));
        return $ip;
    }

    private static function interpolateKnockoutTerms(InstrumentTerms $terms, DateTimeInterface $target_date) : InstrumentTerms
    {
        $interval = $terms->getDate()->diff($target_date)->days;
        $result = new InstrumentTerms();
        $result->setInstrument($terms->getInstrument());
        $result->setDate($target_date);
        $result->setRatio($terms->getRatio());
        if ($terms->getInterestRate() && $interval != 0)
        {
            $factor = (1 + doubleval($terms->getInterestRate())/365.25) ** $interval;
            $result->setStrike(bcmul($terms->getStrike(), strval($factor), 4));
        }
        else
        {
            // nothing to interpolate
            $result->setStrike($terms->getStrike());
        }
        return $result;
    }

    /**
     * Computes a single instrument price from an asset price using the terms
     * @param Instrument $instrument Instrument for which to compute prices
     * @param AssetPrice $asset_price single price with date for which to compute the price
     * @param InstrumentTerms $terms terms used in price computation, time values are interpolated
     */
    public function fromAssetPrice(Instrument $instrument, AssetPrice $asset_price, ?InstrumentTerms $terms = null): ?InstrumentPrice
    {
        if ($instrument->hasTerms() && $terms == null)
        {
            return null;
        }

        $ip = new InstrumentPrice();
        $ip->setInstrument($instrument);
        $ip->setDate($asset_price->getDate());

        // TODO: search best conversion rate near $asset_price->getDate()
        $fx_factor = $this->fx_xchg->latestConversion($instrument->getUnderlying()->getCurrency(), $instrument->getCurrency());
        if ($fx_factor == null)
        {
            return null;
        }

        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                return self::createScaledPrice($instrument, $asset_price, $fx_factor);

            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return null;
                $terms = self::interpolateKnockoutTerms($terms, $asset_price->getDate());
                $factor = doubleval($terms->getRatio()) * $instrument->getDirection() * doubleval($fx_factor);

                $strike = $terms->getStrike();
                if ($strike == null)
                {
                    $strike = "0";
                }

                return self::createKnockoutPrice($instrument, $asset_price, $factor, $strike);
        }
        return null;
    }

    /**
     * Computes multiple instrument prices from asset prices
     * @param \App\Entity\Instrument $instrument Instrument for which to compute prices
     * @param AssetPrice[] $asset_price Array of asset prices for which the instrument price is computed
     * @return InstrumentPrice[] array of instrument prices, of empty if not support/possible
     */
    public function fromAssetPrices(Instrument $instrument, array $asset_price, ?InstrumentTerms $terms = null): array
    {
        if ($instrument->hasTerms() && $terms == null && $instrument->getEusipa() != Instrument::EUSIPA_CFD)
        {
            $it = $this->entityManager->getRepository(InstrumentTerms::class);
            // TODO: use data ranges
            $terms = $it->latestTerms($instrument);
            if ($terms == null)
            {
                return [];
            }
        }

        // TODO: search best conversion rate near $asset_price->getDate()
        $fx_factor = $this->fx_xchg->latestConversion($instrument->getUnderlying()->getCurrency(), $instrument->getCurrency());
        if ($fx_factor == null)
        {
            return [];
        }

        $result = [];
        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                $fn = fn($ap) => self::createScaledPrice($instrument, $ap, $fx_factor);
                $result = array_map($fn, $asset_price);
                break;

            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return [];
                $factor = doubleval($terms->getRatio()) * $instrument->getDirection() * doubleval($fx_factor);

                $strike = $terms->getStrike();
                if ($strike == null)
                {
                    $strike = "0";
                }
                $fn = fn($ap) => self::createKnockoutPrice($instrument, $ap, $factor, $strike);
                $result = array_map($fn, $asset_price);
                break;
        }
        return $result;
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

        return self::fromAssetPrice($instrument, $asset_price, $terms);
    }
/*
    public function mostRecentPrices(Instrument $instrument, \DateTimeInterface $from_date)
    {
        TODO
    }
*/
}
