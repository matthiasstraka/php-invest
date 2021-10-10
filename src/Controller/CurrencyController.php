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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
            ->add('id', IntegerType::class, array('attr' => array('class' => 'form-control'), 'label' => 'ISO 4217 Code'))
            ->add('code', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('name', TextType::class, array('attr' => array('class' => 'form-control')));
        return $form;
    }

    /**
     * @Route("/currency/new", name="new_currency")
     * Method({"GET", "POST"})
     */
    public function new(Request $request) {
        $currency = new Currency(0, "", "");

        $form = $this->buildFormFields($currency)
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary')
            ))
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
     * @Route("/currency/edit/{id}", name="edit_currency")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $currency = $this->getDoctrine()->getRepository(Currency::class)->find($id);

        $form = $this->buildFormFields($currency)
            ->add('save', SubmitType::class, array(
                'label' => 'Store',
                'attr' => array('class' => 'btn btn-primary')
            ))
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
     * @Route("/currency/delete/{id}", name="delete_currency")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $currency = $this->getDoctrine()->getRepository(Currency::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($currency);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();
    }
}
