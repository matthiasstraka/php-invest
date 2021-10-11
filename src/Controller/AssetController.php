<?php

namespace App\Controller;

use App\Entity\Asset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController
{
    /**
     * @Route("/assets", name="asset_list")
     */
    public function index(): Response
    {
        $assets = $this->getDoctrine()
            ->getRepository(Asset::class)
            ->findAll();
        return $this->render('asset/index.html.twig', [
            'controller_name' => 'CountryController',
            'assets' => $assets
        ]);
    }
}
