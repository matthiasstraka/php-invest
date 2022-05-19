<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Execution;
use App\Entity\Instrument;
use App\Entity\InstrumentTerms;
use App\Form\InstrumentType;
use App\Form\InstrumentTermsType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route("/instrument/new", name: "instrument_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request) {
        $asset_id = intval($request->query->get('underlying'));
        
        $instrument = new Instrument();
        
        if ($asset_id > 0)
        {
            $asset = $this->entityManager->getRepository(Asset::class)->find($asset_id);
            if ($asset)
            {
                $instrument->setUnderlying($asset);
            }
        }

        $form = $this->createForm(InstrumentType::class, $instrument, ['underlying_editable' => ($instrument->getUnderlying() == null)]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $this->entityManager->persist($instrument);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_show', ['id' => $instrument->getId()]);
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/{id}/edit", name: "instrument_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Instrument $instrument, Request $request) {
        $form = $this->createForm(InstrumentType::class, $instrument);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $instrument = $form->getData();

            $this->entityManager->persist($instrument);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_show', ["id" => $instrument->getId()]);
        }

        return $this->renderForm('instrument/edit.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/{id}/terms", name: "instrument_terms", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function terms(Instrument $instrument) {
        $terms = $this->entityManager->getRepository(InstrumentTerms::class)->findBy(["instrument" => $instrument]);
        $fields = [
            "cap" => $instrument->hasCap(),
            "strike" => $instrument->hasStrike(),
            "barrier" => $instrument->hasBarrier(),
        ];
        return $this->render('instrument/terms.html.twig', [
            'controller_name' => 'InstrumentController',
            'instrument' => $instrument,
            'terms' => $terms,
            'fields' => $fields,
        ]);
    }

    #[Route("/instrument/{id}/terms/new", name: "instrument_terms_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function termsCreate(Instrument $instrument, Request $request) {        
        $terms = new InstrumentTerms();
        $terms->setInstrument($instrument);

        $latest_terms = $this->entityManager->getRepository(InstrumentTerms::class)->latestTerms($instrument);
        if ($latest_terms) {
            $terms->setFinancingCosts($latest_terms->getFinancingCosts());
            $terms->setRatio($latest_terms->getRatio());
        }
        else
        {
            // TODO: instrument ratios will be deprected soon, copy them in the meanwhile
            $terms->setRatio($instrument->getRatio());
        }

        $terms->setDate(new \DateTime());

        $form = $this->createForm(InstrumentTermsType::class, $terms, ['currency' => $instrument->getUnderlying()->getCurrency()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terms = $form->getData();

            $this->entityManager->persist($terms);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_terms', ["id" => $instrument->getId()]);
        }

        return $this->renderForm('instrument/editterms.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/terms/{id}/edit", name: "instrument_terms_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function termsEdit(InstrumentTerms $terms, Request $request) {
        $instrument = $terms->getInstrument();
        $form = $this->createForm(InstrumentTermsType::class, $terms, ['currency' => $instrument->getUnderlying()->getCurrency()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terms = $form->getData();

            $this->entityManager->persist($terms);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_terms', ["id" => $instrument->getId()]);
        }

        return $this->renderForm('instrument/editterms.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/{id}", name: "instrument_show", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function show(Instrument $instrument) {
        $trades = $this->entityManager
            ->getRepository(Execution::class)
            ->getInstrumentTransactionsForUser($this->getUser(), $instrument);

        //var_dump($trades);

        $total = ['volume' => 0, 'costs' => 0, 'value' => 0, 'price' => null];
        foreach($trades as $trade)
        {
            $total['volume'] = $total['volume'] + $trade['direction'] * $trade['volume'];
            $total['costs'] = $total['costs'] + $trade['costs'];
            if ($trade['direction'] == 0) {
                // Dividends reduce the risk
                $total['value'] = $total['value'] - $trade['total'];
            } else {
                $total['value'] = $total['value'] + $trade['direction'] * $trade['total'];
            }
        }
        if ($total['volume'] != 0)
        {
            $total['price'] = $total['value'] / $total['volume'];
        }

        $terms = $this->entityManager->getRepository(InstrumentTerms::class)->latestTerms($instrument);

        return $this->render('instrument/show.html.twig', [
            'controller_name' => 'InstrumentController',
            'instrument' => $instrument,
            'trades' => $trades,
            'terms' => $terms,
            'total' => $total,
        ]);
    }

    #[Route("/instrument/{id}", name: "instrument_delete", methods: ["DELETE"])]
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
