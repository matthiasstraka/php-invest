<?php

namespace App\Controller;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CountryController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/country", name: "country_list")]
    public function index(): Response
    {
        $countries = $this->entityManager
            ->getRepository(Country::class)
            ->findAll();
        return $this->render('country/index.html.twig', [
            'controller_name' => 'CountryController',
            'countries' => $countries
        ]);
    }

    #[Route("/country/new", name: "country_new")]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request) {
        $country = new Country("");
        
        $form = $this->createFormBuilder($country)
            ->add('code', TextType::class, ['label' => 'ISO 3166-1 Code'])
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $country = $form->getData();

            $this->entityManager->persist($country);
            $this->entityManager->flush();

            $this->addFlash('success', "Country {$country->getCode()} added.");

            return $this->redirectToRoute('country_list');
        }

        return $this->renderForm('country/edit.html.twig', ['form' => $form]);
    }

    #[Route('/country/{id}', name: 'country_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Country $country) {
        try
        {
            $this->entityManager->remove($country);
            $this->entityManager->flush();
            $this->addFlash('success', "Country {$country->getCode()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
