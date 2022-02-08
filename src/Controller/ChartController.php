<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/chart/asset_price/{id}", name: "chart_asset_price")]
    public function assetPrice(Asset $asset): JsonResponse
    {
        $prices = $this->entityManager
            ->getRepository(AssetPrice::class)
            ->findBy(['asset' => $asset], ['date' => 'ASC']);

        $data = array_map(fn($ap) => [
            "x" => $ap->getDate()->format("Y-m-d"),
            "y" => floatval($ap->getClose())],
            $prices);

        return new JsonResponse($data);
    }
}
