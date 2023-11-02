<?php

namespace App\Service;

use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Entity\InstrumentPrice;
use App\Entity\InstrumentTerms;
use App\Service\CurrencyConversionService;
use App\Controller\InstrumentController;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class InstrumentPriceService
{
    private $entityManager;
    private $fx_xchg;
    private $instrumentController;

    public function __construct(EntityManagerInterface $entityManager, CurrencyConversionService $fx_xchg, InstrumentController $instrumentController)
    {
        $this->entityManager = $entityManager;
        $this->fx_xchg = $fx_xchg;
        $this->instrumentController = $instrumentController;
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
        if ($terms == null && $this->instrumentController->availableTerms($instrument))
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

        $ratio = 1;
        if ($terms)
        {
            $ratio = doubleval($terms->getRatio());
        }

        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                return self::createScaledPrice($instrument, $asset_price, $fx_factor);

            case Instrument::EUSIPA_TRACKER:
                //TODO: deduct management fees, consider direction
                return self::createScaledPrice($instrument, $asset_price, $fx_factor * $ratio);

            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return null;
                $terms = self::interpolateKnockoutTerms($terms, $asset_price->getDate());
                $factor = $ratio * $instrument->getDirection() * doubleval($fx_factor);

                $strike = $terms->getStrike() ?? "0";
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
        if ($terms == null && $this->instrumentController->availableTerms($instrument) && $instrument->getEusipa() != Instrument::EUSIPA_CFD)
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

        $ratio = 1;
        if ($terms)
        {
            $ratio = doubleval($terms->getRatio());
        }

        $result = [];
        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
                $fn = fn($ap) => self::createScaledPrice($instrument, $ap, $fx_factor);
                $result = array_map($fn, $asset_price);
                break;

            case Instrument::EUSIPA_TRACKER:
                //TODO: deduct management fees, consider direction
                $fn = fn($ap) => self::createScaledPrice($instrument, $ap, $ratio * doubleval($fx_factor));
                $result = array_map($fn, $asset_price);
                break;

            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
            case Instrument::EUSIPA_CONSTANT_LEVERAGE:
                if ($terms == null)
                    return [];
                $factor = $ratio * $instrument->getDirection() * doubleval($fx_factor);

                $strike = $terms->getStrike() ?? "0";
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

        if ($terms == null && $this->instrumentController->availableTerms($instrument))
        {
            $it = $this->entityManager->getRepository(InstrumentTerms::class);
            $terms = $it->latestTerms($instrument);
        }

        return self::fromAssetPrice($instrument, $asset_price, $terms);
    }

    public function computeLeverage(Instrument $instrument, ?AssetPrice $asset_price, ?InstrumentTerms $terms) : ?float
    {
        switch ($instrument->getEusipa()) {
            case Instrument::EUSIPA_UNDERLYING:
            case Instrument::EUSIPA_CFD:
            case Instrument::EUSIPA_TRACKER:
                return 1;

            case Instrument::EUSIPA_MINIFUTURE:
            case Instrument::EUSIPA_KNOCKOUT:
                if ($terms == null || $asset_price == null)
                    return null;
                $terms = self::interpolateKnockoutTerms($terms, $asset_price->getDate());
                $strike = $terms->getStrike();
                if ($strike == null)
                    return null;
                $price = doubleval($asset_price->getClose());

                return $instrument->getDirection() * $price / ($price - doubleval($strike));

            case Instrument::EUSIPA_CLASS_CONSTANT_LEVERAGE:
                // TODO: store leverage in terms?
                return null;

            default:
                return null;
        }
    }
/*
    public function mostRecentPrices(Instrument $instrument, \DateTimeInterface $from_date)
    {
        TODO
    }
*/
}
