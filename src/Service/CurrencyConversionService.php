<?php

namespace App\Service;

use App\Entity\AssetPrice;
use Doctrine\ORM\EntityManagerInterface;

class CurrencyConversionService
{
    // Map of FX ISINS that allow conversion of a currency to USD
    const FX_USD_ISINS = [
        'AUD' => 'XC000A0E4TC6',
        'EUR' => 'EU0009652759',
        'GPB' => 'GB0031973075',
    ];

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function latestConversion(string $from, string $to, int $scale = 4): ?string
    {
        if ($from == $to)
        {
            return "1";
        }

        if ($from == "USD")
        {
            $result = $this->latestConversion($to, "USD");
            return $result ? bcdiv("1", $result, $scale) : null;
        }

        if ($to == "USD" && array_key_exists($from, self::FX_USD_ISINS))
        {
            $ap = $this->entityManager->getRepository(AssetPrice::class);
            $asset_price = $ap->latestPriceByIsin(self::FX_USD_ISINS[$from]);
            if ($asset_price == null)
            {
                return null;
            }

            return $asset_price->getClose();
        }
        
        // find exchange rate A -> USD -> B
        $fx_a = $this->latestConversion($from, "USD", 6);
        if ($fx_a != null)
        {
            $fx_b = $this->latestConversion("USD", $to, 6);
            if ($fx_b != null)
            {
                return bcmul($fx_a, $fx_b, $scale);
            }
        }

        return null;
    }
}
