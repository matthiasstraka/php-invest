<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CountryController extends AbstractController
{
    /**
     * @Route("/country", name="country")
     */
    public function index(): Response
    {
        $countries = $this->getDoctrine()
            ->getRepository(\App\Entity\Country::class)
            ->findAll();
        return $this->render('country/index.html.twig', [
            'controller_name' => 'CountryController',
            'countries' => $countries
        ]);
    }

    /**
     * @Route("/country/delete/{id}")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $country = $this->getDoctrine()->getRepository(\App\Entity\Country::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($country);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();
    }
}
