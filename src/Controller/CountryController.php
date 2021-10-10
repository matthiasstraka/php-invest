<?php

namespace App\Controller;

use App\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    protected function buildFormFields($country) {
        $form = $this->createFormBuilder($country)
            ->add('id', IntegerType::class, array('attr' => array('class' => 'form-control'), 'label' => 'ISO 3166-1 Code'))
            ->add('name', TextType::class, array('attr' => array('class' => 'form-control')));
        return $form;
    }

    /**
     * @Route("/country/new", name="country_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $country = new Country(0, "");

        $form = $this->buildFormFields($country)
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

            return $this->redirectToRoute('country_list');
        }

        return $this->renderForm('country/edit.html.twig', array(
            'form' => $form
        ));
    }

    /**
     * @Route("/country/{id}", name="country_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id) {
        $country = $this->getDoctrine()->getRepository(Country::class)->find($id);

        $form = $this->buildFormFields($country)
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

            return $this->redirectToRoute('country_list');
        }

        return $this->renderForm('country/edit.html.twig', array(
            'form' => $form
        ));
    }

    /**
     * @Route("/country/{id}", name="country_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id) {
        $country = $this->getDoctrine()->getRepository(Country::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($country);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();
    }
}
