<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Instrument;
use App\Service\InstrumentPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected static function extractData(array $prices, bool $close_only): array
    {
        if ($close_only)
        {
            $map_fn = fn($ap) => [
                "x" => $ap->getDate()->getTimestamp() * 1000,
                "y" => floatval($ap->getClose())
                ];
        } else {
            $map_fn = fn($ap) => [
                "x" => $ap->getDate()->getTimestamp() * 1000,
                "o" => floatval($ap->getOpen()),
                "h" => floatval($ap->getHigh()),
                "l" => floatval($ap->getLow()),
                "c" => floatval($ap->getClose())
                ];
        }
        return array_map($map_fn, $prices);
    }

    #[Route("/chart/asset_price/{id}", name: "chart_asset_price")]
    public function assetPrice(Request $request, Asset $asset): JsonResponse
    {
        $datefrom = intval($request->query->get('from', '0'));
        if ($datefrom > 0)
        {
            $datefrom = \DateTime::createFromFormat("Ymd", strval($datefrom));
        }
        else
        {
            $datefrom = null;
        }

        $type = $request->query->get('type');
        if (!$type)
        {
            $type = "ohlc";
        }

        $repo = $this->entityManager->getRepository(AssetPrice::class);

        if ($datefrom)
        {
            $prices = $repo->mostRecentPrices($asset, $datefrom);
        }
        else
        {
            $prices = $repo->findBy(['asset' => $asset], ['date' => 'ASC']);
        }

        $data = self::extractData($prices, $type == "close");

        $response = new JsonResponse($data);
        $response->setPublic();
        $response->setMaxAge(3600);
        return $response;
    }

    #[Route("/chart/instrument_price/{id}", name: "chart_instrument_price")]
    public function instrumentPrice(Request $request, Instrument $instrument, InstrumentPriceService $ip_service): JsonResponse
    {
        $datefrom = intval($request->query->get('from', '0'));
        if ($datefrom > 0)
        {
            $datefrom = \DateTime::createFromFormat("Ymd", strval($datefrom));
        }
        else
        {
            $datefrom = null;
        }

        $type = $request->query->get('type');
        if (!$type)
        {
            $type = "ohlc";
        }

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

        $data = self::extractData($instrument_prices, $type == "close");

        $response = new JsonResponse($data);
        $response->setPublic();
        $response->setMaxAge(3600);
        return $response;
    }
}
