<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Country;
use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            'controller_name' => 'AssetController',
            'assets' => $assets
        ]);
    }

    protected function buildFormFields($asset) {
        $form = $this->createFormBuilder($asset)
            ->add('isin', TextType::class, ['label' => 'ISIN'])
            ->add('name', TextType::class)
            ->add('symbol', TextType::class, ['label' => 'Symbol (e.g. Ticker symbol)'])
            ->add('type', ChoiceType::class, ['choices' => [
                'Bond' => Asset::TYPE_BOND,
                'Commodity' => Asset::TYPE_COMMODITY,
                'Fonds' => Asset::TYPE_FONDS,
                'Foreign Exchange' => Asset::TYPE_FX,
                'Index' => Asset::TYPE_INDEX,
                'Stock' => Asset::TYPE_STOCK,
                ]])
            ->add('currency', EntityType::class, ['class' => Currency::class])
            ->add('country', EntityType::class, ['class' => Country::class, 'required' => false])
        ;
        return $form;
    }

    /**
     * @Route("/assets/new", name="asset_new", methods={"GET", "POST"})
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

            $this->addFlash('success', 'Asset created.');

            return $this->redirectToRoute('asset_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }
}
