<?php

namespace App\Twig;

use App\Entity\Asset;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\ORM\EntityManagerInterface;

class AppExtension extends AbstractExtension
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('flag_icon', [$this, 'flagIcon'], ['is_safe' => ['html']]),
            new TwigFilter('symbol_badge', [$this, 'symbolBadge'], ['is_safe' => ['html']]),
            new TwigFilter('asset_from_isin', [$this, 'assetFromIsin'], ['is_safe' => ['html']]),
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

    public function assetFromIsin(?string $isin): int
    {
        if ($isin == null)
            return 0;

        $isin = strtoupper($isin);

        $repo = $this->entityManager->getRepository(Asset::class);
        $asset = $repo->findOneBy(['ISIN' => $isin]);
        if ($asset)
        {
            return $asset->getId();
        }
        return 0;
    }
}
