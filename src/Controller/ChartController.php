<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetPrice;
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

    #[Route("/chart/asset_price/{id}", name: "chart_asset_price")]
    public function assetPrice(Request $request, Asset $asset): JsonResponse
    {
        $datefrom = intval($request->query->get('from', '0'));
        if ($datefrom > 0)
        {
            $datefrom = \DateTime::createFromFormat("Ymd", $datefrom);
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

        if ($type == "close")
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
        $data = array_map($map_fn, $prices);

        $response = new JsonResponse($data);
        $response->setPublic();
        $response->setMaxAge(3600);
        return $response;
    }
}
