<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
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
            
        $country = strtolower($country);
        return "<span class=\"fi fi-{$country}\"></span>";
    }
}
