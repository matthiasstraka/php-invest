<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    /**
     * @Route("/currency", name="currency_list")
     */
    public function index(): Response
    {
        $currencies = $this->getDoctrine()
            ->getRepository(Currency::class)
            ->findAll();

        return $this->render('currency/index.html.twig', [
            'controller_name' => 'CurrencyController',
            'currencies' => $currencies
        ]);
    }

    protected function buildFormFields($currency) {
        $form = $this->createFormBuilder($currency)
            ->add('id', IntegerType::class, ['label' => 'ISO 4217 Code'])
            ->add('code', TextType::class)
            ->add('name', TextType::class);
        return $form;
    }

    /**
     * @Route("/currency/new", name="currency_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $currency = new Currency(0, "", "");

        $form = $this->buildFormFields($currency)
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $country = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($country);
            $entityManager->flush();

            return $this->redirectToRoute('currency_list');
        }

        return $this->renderForm('currency/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/currency/{id}", name="currency_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id) {
        $currency = $this->getDoctrine()->getRepository(Currency::class)->find($id);

        $form = $this->buildFormFields($currency)
            ->add('save', SubmitType::class, ['label' => 'Store', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $country = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($country);
            $entityManager->flush();

            return $this->redirectToRoute('currency_list');
        }

        return $this->renderForm('currency/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/currency/{id}", name="currency_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id) {
        $currency = $this->getDoctrine()->getRepository(Currency::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($currency);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();
    }
}
