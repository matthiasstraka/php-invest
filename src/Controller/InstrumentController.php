<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Instrument;
use App\Forms\Type\InstrumentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstrumentController extends AbstractController
{
    /**
     * @Route("/instruments", name="instrument_list")
     */
    public function index(): Response
    {
        $instruments = $this->getDoctrine()
            ->getRepository(Instrument::class)
            ->findAll();
        return $this->render('instrument/index.html.twig', [
            'controller_name' => 'InstrumentController',
            'instruments' => $instruments
        ]);
    }

    /**
     * @Route("/instruments/new", name="instrument_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $instrument = new Instrument();

        $form = $this->createForm(InstrumentType::class, $instrument);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($instrument);
            $entityManager->flush();

            $this->addFlash('success', "Instrument {$instrument->getName()} added.");

            return $this->redirectToRoute('instrument_list');
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/instruments/edit/{id}", name="instrument_edit", methods={"GET", "POST"})
     */
    public function edit(Instrument $instrument, Request $request) {
        $form = $this->createForm(InstrumentType::class, $instrument);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($instrument);
            $em->flush();

            $this->addFlash('success', 'Instrument edited.');

            return $this->redirectToRoute('instrument_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/instruments/{id}", name="instrument_delete", methods={"DELETE"})
     */
    public function delete(Request $request, string $id) {
        $entityManager = $this->getDoctrine()->getManager();
        $obj = $entityManager->find(Instrument::class, $id);

        if ($obj == null)
        {
            return new JsonResponse(['message' => "Instrument $id not found"], 404);
        }

        try
        {
            $entityManager->remove($obj);
            $entityManager->flush();
            $this->addFlash('success', "Instrument {$obj->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
