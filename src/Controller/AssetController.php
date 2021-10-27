<?php

namespace App\Controller;

use App\Forms\Type\AssetType;
use App\Entity\Asset;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @IsGranted("ROLE_USER")
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

    /**
     * @Route("/assets/edit/{id}", name="asset_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Asset $asset, Request $request) {
        $form = $this->createForm(AssetType::class, $asset);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($asset);
            $em->flush();

            $this->addFlash('success', 'Asset edited.');

            return $this->redirectToRoute('asset_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }
    
    /**
     * @Route("/assets/{id}", name="asset_show", methods={"GET"})
     */
    public function show(Asset $asset) {
        $this->addFlash('success', "Not implemented, but found {$asset->getName()}");

        return $this->redirectToRoute('asset_list');
    }

    /**
     * @Route("/assets/{id}", name="asset_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Asset $asset) {
        try
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($asset);
            $entityManager->flush();
            $this->addFlash('success', "Asset {$asset->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
