<?php

namespace App\Controller;

use App\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CountryController extends AbstractController
{
    /**
     * @Route("/country", name="country_list")
     */
    public function index(): Response
    {
        $countries = $this->getDoctrine()
            ->getRepository(Country::class)
            ->findAll();
        return $this->render('country/index.html.twig', [
            'controller_name' => 'CountryController',
            'countries' => $countries
        ]);
    }

    /**
     * @Route("/country/new", name="country_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $country = new Country("");
        
        $form = $this->createFormBuilder($country)
            ->add('code', TextType::class, ['label' => 'ISO 3166-1 Code'])
            ->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $country = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($country);
            $entityManager->flush();

            $this->addFlash('success', "Country $code added.");

            return $this->redirectToRoute('country_list');
        }

        return $this->renderForm('country/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/country/{code}", name="country_delete", methods={"DELETE"})
     */
    public function delete(Request $request, string $code) {
        $entityManager = $this->getDoctrine()->getManager();
        $country = $entityManager->find(Country::class, $code);

        if ($country == null)
        {
            return new JsonResponse(['message' => "Country $code not found"], 404);
        }

        try
        {
            $entityManager->remove($country);
            $entityManager->flush();
            $this->addFlash('success', "Country $code deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
