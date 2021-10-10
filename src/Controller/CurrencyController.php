<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CurrencyController extends AbstractController
{
    /**
     * @Route("/currency", name="currency")
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
     * @Route("/currency/delete/{id}")
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
