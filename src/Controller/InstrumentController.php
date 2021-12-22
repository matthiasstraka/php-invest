<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Instrument;
use App\Entity\Transaction;
use App\Form\InstrumentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class InstrumentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/instruments", name: "instrument_list")]
    public function index(): Response
    {
        $instruments = $this->entityManager
            ->getRepository(Instrument::class)
            ->findAll();
        return $this->render('instrument/index.html.twig', [
            'controller_name' => 'InstrumentController',
            'instruments' => $instruments
        ]);
    }

    #[Route("/instruments/new", name: "instrument_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request) {
        $instrument = new Instrument();

        $form = $this->createForm(InstrumentType::class, $instrument);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $this->entityManager->persist($instrument);
            $this->entityManager->flush();

            $this->addFlash('success', "Instrument {$instrument->getName()} added.");

            return $this->redirectToRoute('instrument_list');
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }

    #[Route("/instruments/edit/{id}", name: "instrument_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Instrument $instrument, Request $request) {
        $form = $this->createForm(InstrumentType::class, $instrument);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $this->entityManager->persist($instrument);
            $this->entityManager->flush();

            $this->addFlash('success', 'Instrument edited.');

            return $this->redirectToRoute('instrument_list');
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }

    #[Route("/instruments/{id}", name: "instrument_show", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function show(?UserInterface $user, Instrument $instrument) {
        $trades = $this->entityManager
            ->getRepository(Transaction::class)
            ->getInstrumentTransactionsForUser($user, $instrument);

        //var_dump($trades);

        return $this->render('instrument/show.html.twig', [
            'controller_name' => 'InstrumentController',
            'instrument' => $instrument,
            'trades' => $trades,
        ]);
    }

    #[Route("/instruments/{id}", name: "instrument_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete(Instrument $instrument) {
        try
        {
            $this->entityManager->remove($instrument);
            $this->entityManager->flush();
            $this->addFlash('success', "Instrument {$instrument->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
