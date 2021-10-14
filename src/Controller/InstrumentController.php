<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Instrument;
use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstrumentController extends AbstractController
{
    /**
     * @Route("/instruments", name="instrument_list")
     */
    public function index(): Response
    {
        $instruments = $this->getDoctrine()
            ->getRepository(Instrument::class)
            ->findAll();
        return $this->render('instrument/index.html.twig', [
            'controller_name' => 'InstrumentController',
            'instruments' => $instruments
        ]);
    }

    protected function buildFormFields($asset) {
        $form = $this->createFormBuilder($asset)
            ->add('isin', TextType::class, ['label' => 'ISIN'])
            ->add('name', TextType::class)
            ->add('underlying', EntityType::class, ['class' => Asset::class])
            ->add('currency', EntityType::class, ['class' => Currency::class])
        ;
        return $form;
    }

    /**
     * @Route("/instruments/new", name="instrument_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $instrument = new Instrument();

        $form = $this->buildFormFields($instrument)
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            var_dump($instrument);
            $entityManager->persist($instrument);
            $entityManager->flush();

            return $this->redirectToRoute('instrument_list');
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }
}
