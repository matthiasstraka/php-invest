<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('flag_icon', [$this, 'flagIcon'], ['is_safe' => ['html']]),
            new TwigFilter('symbol_badge', [$this, 'symbolBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function flagIcon(?string $country): string
    {
        if ($country == null)
            return "";

        $country = strtolower($country);
        return "<i class=\"fi fi-{$country}\"></i>";
    }

    public function symbolBadge(?string $symbol): string
    {
        if ($symbol == null)
            return "";

        $symbol = strtoupper($symbol);
        return "<span class=\"badge bg-secondary\">$symbol</span>";
    }
}
