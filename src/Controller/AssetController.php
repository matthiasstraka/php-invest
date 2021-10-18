<?php

namespace App\Controller;

use App\Forms\Type\AssetType;
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
    
    /**
     * @Route("/assets/{code}", name="asset_show", methods={"GET"})
     */
    public function show(Request $request, string $code) {
        $entityManager = $this->getDoctrine()->getManager();
        $asset = $entityManager->find(Asset::class, $code);

        if ($asset == null)
        {
            //throw $this->createNotFoundException('No asset found for id '.$code);

            $this->addFlash('error', 'Asset not found.');

            return $this->redirectToRoute('asset_list');
        }

        $this->addFlash('success', "Not implemented, but found {$asset->getName()}");

        return $this->redirectToRoute('asset_list');
    }

    /**
     * @Route("/assets/{code}", name="asset_delete", methods={"DELETE"})
     */
    public function delete(Request $request, string $code) {
        $entityManager = $this->getDoctrine()->getManager();
        $obj = $entityManager->find(Asset::class, $code);

        if ($obj == null)
        {
            return new JsonResponse(['message' => "Asset $code not found"], 404);
        }

        try
        {
            $entityManager->remove($obj);
            $entityManager->flush();
            $this->addFlash('success', "Asset {$obj->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
