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

    public function fromAssetPrices(Instrument $instrument, array $asset_price, ?InstrumentTerms $terms = null): array
    {
        if ($instrument->hasTerms() && $terms == null)
        {
            return [];
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
                foreach ($asset_price as $ap) {
                    $result[] = self::createScaledPrice($instrument, $ap, $fx_factor);
                }
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
                foreach ($asset_price as $ap) {
                    $result[] = self::createKnockoutPrice($instrument, $ap, $factor, $strike);
                }
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
