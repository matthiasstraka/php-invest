<?php

namespace App\Controller;

use App\Form\Type\AssetType;
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
            'controller_name' => 'AssetController',
            'assets' => $assets
        ]);
    }

    /**
     * @Route("/assets/new", name="asset_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $asset = new Asset();

        $form = $this->createForm(AssetType::class, $asset);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($asset);
            $entityManager->flush();

            $this->addFlash('success', 'Asset created.');

            return $this->redirectToRoute('asset_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }
}
