<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    
    /**
     * @Route("/currency/new", name="currency_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $currency = new Currency("");
        
        $form = $this->createFormBuilder($currency)
            ->add('code', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $currency = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($currency);
            $entityManager->flush();

            $this->addFlash('success', "Currency {$currency->getCode()} added.");

            return $this->redirectToRoute('currency_list');
        }

        return $this->renderForm('currency/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/currency/{id}", name="currency_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Currency $currency) {
        try
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($currency);
            $entityManager->flush();
            $this->addFlash('success', "Currency {$currency->getCode()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
