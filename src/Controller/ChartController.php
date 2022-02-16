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
        $daterange = intval($request->query->get('daterange'));
        if ($daterange == 0)
        {
            $daterange = null;
        }

        $type = $request->query->get('type');
        if (!$type)
        {
            $type = "ohlc";
        }

        $repo = $this->entityManager->getRepository(AssetPrice::class);

        if ($daterange)
        {
            $today = new \DateTime();
            $fromdate = $today->sub(new \DateInterval("P{$daterange}D"));
            $prices = $repo->mostRecentPrices($asset, $fromdate);
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
        $response->setMaxAge(60); // TODO: this might be a problem when we update prices
        return $response;
    }
}
