<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Service\InstrumentPriceService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class ChartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected static function extractData(array $prices, string $type): array
    {
        switch ($type)
        {
            case "open":
                $map_fn = fn($ap) => [
                    "x" => $ap->getDate()->getTimestamp() * 1000,
                    "y" => floatval($ap->getOpen())
                    ];
                break;
            case "high":
                $map_fn = fn($ap) => [
                    "x" => $ap->getDate()->getTimestamp() * 1000,
                    "y" => floatval($ap->getHigh())
                    ];
                break;
            case "low":
                $map_fn = fn($ap) => [
                    "x" => $ap->getDate()->getTimestamp() * 1000,
                    "y" => floatval($ap->getLow())
                    ];
                break;
            case "close":
                $map_fn = fn($ap) => [
                    "x" => $ap->getDate()->getTimestamp() * 1000,
                    "y" => floatval($ap->getClose())
                    ];
                break;
            case "ohlc":
                $map_fn = fn($ap) => [
                    "x" => $ap->getDate()->getTimestamp() * 1000,
                    "o" => floatval($ap->getOpen()),
                    "h" => floatval($ap->getHigh()),
                    "l" => floatval($ap->getLow()),
                    "c" => floatval($ap->getClose())
                    ];
                break;
            default:
                throw new InvalidArgumentException("Unsupported chart type '$type'");
        }
        return array_map($map_fn, $prices);
    }

    #[Route("/api/asset_price/{id}", name: "chart_asset_price")]
    #[Cache(public: true, maxage: 3600)]
    public function assetPrice(Request $request, Asset $asset): JsonResponse
    {
        $datefrom = intval($request->query->get('from', '0'));
        if ($datefrom > 0)
        {
            $datefrom = \DateTime::createFromFormat("Ymd", strval($datefrom))->setTime(0, 0);
        }
        else
        {
            $datefrom = null;
        }

        $type = $request->query->get('type') ?? "ohlc";

        $repo = $this->entityManager->getRepository(AssetPrice::class);

        if ($datefrom)
        {
            $prices = $repo->mostRecentPrices($asset, $datefrom);
        }
        else
        {
            $prices = $repo->findBy(['asset' => $asset], ['date' => 'ASC']);
        }

        $data = self::extractData($prices, $type);

        return new JsonResponse($data);
    }

    #[Route("/api/instrument_price/{id}", name: "chart_instrument_price")]
    #[Cache(public: true, maxage: 3600)]
    public function instrumentPrice(Request $request, Instrument $instrument, InstrumentPriceService $ip_service): JsonResponse
    {
        $datefrom = intval($request->query->get('from', '0'));
        if ($datefrom > 0)
        {
            $datefrom = \DateTime::createFromFormat("Ymd", strval($datefrom))->setTime(0, 0);
        }
        else
        {
            $datefrom = null;
        }

        $type = $request->query->get('type') ?? 'ohlc';

        $asset = $instrument->getUnderlying();
        $repo = $this->entityManager->getRepository(AssetPrice::class);
        if ($datefrom)
        {
            $asset_prices = $repo->mostRecentPrices($asset, $datefrom);
        }
        else
        {
            $asset_prices = $repo->findBy(['asset' => $asset], ['date' => 'ASC']);
        }

        $instrument_prices = $ip_service->fromAssetPrices($instrument, $asset_prices);

        $data = self::extractData($instrument_prices, $type);

        return new JsonResponse($data);
    }
}
