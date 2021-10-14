<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    protected function buildFormFields($asset) {
        $form = $this->createFormBuilder($asset)
            ->add('isin', TextType::class)
            ->add('name', TextType::class)
            ->add('symbol', TextType::class)
            ->add('assettype', EntityType::class, [
                'class' => AssetType::class
            ])
            ->add('currency', TextType::class)
            ->add('country', TextType::class)
        ;
        return $form;
    }

    /**
     * @Route("/assets/new", name="country_asset", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $asset = new Asset();

        $form = $this->buildFormFields($asset)
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($asset);
            $entityManager->flush();

            return $this->redirectToRoute('asset_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }
}
