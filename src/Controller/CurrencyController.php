<?php

namespace App\Controller;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            ->add('code', TextType::class, ['label' => 'ISO 4217 Code (e.g. US)', 'required' => true])
            ->add('isinUsd', TextType::class, ['label' => 'ISIN', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $form->getData();

            $this->entityManager->persist($currency);
            $this->entityManager->flush();

            return $this->redirectToRoute('currency_list');
        }

        return $this->render('currency/edit.html.twig', ['form' => $form]);
    }

    #[Route('/api/currency/{id}', name: 'currency_read', methods: ['GET'])]
    #[Cache(public: true, maxage: 60)]
    public function read(?Currency $currency) : JsonResponse {
        if (is_null($currency))
        {
            return new JsonResponse(['message' => 'Currency not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse([
            'code' => $currency->getCode(),
            'isin' => $currency->getIsinUsd(),
        ]);
    }

    #[Route('/api/currency/{id}', name: 'currency_delete', methods: ['DELETE'])]
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
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
