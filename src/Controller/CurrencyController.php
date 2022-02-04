<?php

namespace App\Controller;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/currency', name: 'currency_list')]
    public function index(): Response
    {
        $currencies = $this->entityManager
            ->getRepository(Currency::class)
            ->findAll();

        return $this->render('currency/index.html.twig', [
            'controller_name' => 'CurrencyController',
            'currencies' => $currencies
        ]);
    }

    #[Route('/currency/new', name: 'currency_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request) {
        $currency = new Currency("");
        
        $form = $this->createFormBuilder($currency)
            ->add('code', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $form->getData();

            $this->entityManager->persist($currency);
            $this->entityManager->flush();

            return $this->redirectToRoute('currency_list');
        }

        return $this->renderForm('currency/edit.html.twig', ['form' => $form]);
    }

    #[Route('/currency/{id}', name: 'currency_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Currency $currency) {
        try
        {
            $this->entityManager->remove($currency);
            $this->entityManager->flush();
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
