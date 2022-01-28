<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    const FLAG_MAP = [
        'DE' => 'germany',
        'US' => 'us',
    ];

    public function getFilters()
    {
        return [
            new TwigFilter('flag_icon', [$this, 'flagIcon'], ['is_safe' => ['html']]),
        ];
    }

    public function flagIcon(?string $country): string
    {
        if ($country == null)
            return "";
            
        $country = strtoupper($country);
        if (array_key_exists($country, self::FLAG_MAP))
        {
            $class = self::FLAG_MAP[$country];
            return "<i class=\"flag flag-{$class}\"></i>";
        }
        return "";
    }
}
