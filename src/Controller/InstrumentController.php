<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\Execution;
use App\Entity\Instrument;
use App\Entity\InstrumentPrice;
use App\Entity\InstrumentTerms;
use App\Form\InstrumentType;
use App\Form\InstrumentTermsType;
use App\Service\InstrumentPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InstrumentController extends AbstractController
{
    private $entityManager;

    private function getAvailableTerms(int $eusipa)
    {
        $definitions = $this->getParameter("app.instruments");
        if (array_key_exists($eusipa, $definitions))
        {
            $def = $definitions[$eusipa];
            return isset($def['terms']) ? $def['terms'] : [];
        }
        return [];
    }
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/instruments", name: "instrument_list")]
    public function index(): Response
    {
        $visible_instruments = [Instrument::STATUS_ACTIVE, Instrument::STATUS_BARRIER_BREACHED];
        $instruments = $this->entityManager
            ->getRepository(Instrument::class)
            ->findBy(['status' => $visible_instruments]);
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

        return $this->render('instrument/edit.html.twig', ['form' => $form]);
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

        return $this->render('instrument/edit.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/{id}/terms", name: "instrument_terms", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function terms(Instrument $instrument) {
        $terms = $this->entityManager->getRepository(InstrumentTerms::class)->findBy(["instrument" => $instrument]);

        $ap = $this->entityManager->getRepository(AssetPrice::class);
        $last_asset_price = $ap->latestPrice($instrument->getUnderlying());

        $chartdateto = $last_asset_price ? $last_asset_price->getDate() : null;
        $chartdatefrom = $last_asset_price ? $last_asset_price->getDate()->modify("-365 day") : null;

        $chart_strike = [];
        $chart_barrier = [];
        $chart_cap = [];
        $chart_bonus = [];
        $chart_reverse = [];
        if ($chartdatefrom) {
            foreach($terms as $term) {
                $tick = $term->getDate()->getTimestamp() * 1000;
                if ($term->getStrike()) {
                    $chart_strike[] = ['x' => $tick, 'y' => $term->getStrike()];
                }
                if ($term->getBarrier()) {
                    $chart_barrier[] = ['x' => $tick, 'y' => $term->getBarrier()];
                }
                if ($term->getCap()) {
                    $chart_cap[] = ['x' => $tick, 'y' => $term->getCap()];
                }
                if ($term->getBonusLevel()) {
                    $chart_bonus[] = ['x' => $tick, 'y' => $term->getBonusLevel()];
                }
                if ($term->getReverseLevel()) {
                    $chart_reverse[] = ['x' => $tick, 'y' => $term->getReverseLevel()];
                }
            }

            // if there is only one point in a series, span it across the whole time range
            $series_extend = function (array $series) use ($chartdatefrom, $chartdateto) {
                if (count($series) == 0) {
                    return null;
                }
                if (count($series) == 1) {
                    $val = $series[0]['y'];
                    return [
                        ['x' => $chartdatefrom->getTimestamp() * 1000, 'y' => $val],
                        ['x' => $chartdateto->getTimestamp() * 1000, 'y' => $val],
                    ];
                }
                return $series;
            };
            $chart_strike = $series_extend($chart_strike);
            $chart_barrier = $series_extend($chart_barrier);
            $chart_cap = $series_extend($chart_cap);
            $chart_bonus = $series_extend($chart_bonus);
            $chart_reverse = $series_extend($chart_reverse);
        }

        return $this->render('instrument/terms.html.twig', [
            'controller_name' => 'InstrumentController',
            'instrument' => $instrument,
            'terms' => $terms,
            'chart_datefrom' => $chartdatefrom,
            'chart_strike' => $chart_strike,
            'chart_barrier' => $chart_barrier,
            'chart_cap' => $chart_cap,
            'chart_bonus' => $chart_bonus,
            'chart_reverse' => $chart_reverse,
            'available_terms' => $this->getAvailableTerms($instrument->getEusipa())
        ]);
    }

    #[Route("/instrument/{id}/terms/new", name: "instrument_terms_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function termsCreate(Instrument $instrument, Request $request) {        
        $terms = new InstrumentTerms();
        $terms->setInstrument($instrument);

        $latest_terms = $this->entityManager->getRepository(InstrumentTerms::class)->latestTerms($instrument);
        if ($latest_terms) {
            $terms->setRatio($latest_terms->getRatio());
            $terms->setInterestRate($latest_terms->getInterestRate());
            $terms->setMargin($latest_terms->getMargin());
        }

        $terms->setDate(new \DateTime());

        $form = $this->createForm(InstrumentTermsType::class, $terms, [
            'currency' => $instrument->getUnderlying()->getCurrency(),
            'available_terms' => $this->getAvailableTerms($instrument->getEusipa())
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terms = $form->getData();

            $this->entityManager->persist($terms);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_terms', ["id" => $instrument->getId()]);
        }

        return $this->render('instrument/editterms.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/terms/{id}/edit", name: "instrument_terms_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function termsEdit(InstrumentTerms $terms, Request $request) {
        $instrument = $terms->getInstrument();
        $form = $this->createForm(InstrumentTermsType::class, $terms, [
            'currency' => $instrument->getUnderlying()->getCurrency(),
            'available_terms' => $this->getAvailableTerms($instrument->getEusipa())
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terms = $form->getData();

            $this->entityManager->persist($terms);
            $this->entityManager->flush();

            return $this->redirectToRoute('instrument_terms', ["id" => $instrument->getId()]);
        }

        return $this->render('instrument/editterms.html.twig', ['form' => $form]);
    }

    #[Route("/instrument/{id}", name: "instrument_show", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function show(Instrument $instrument, InstrumentPriceService $ip_service) {
        $trades = $this->entityManager->getRepository(Execution::class)
            ->getInstrumentTransactionsForUser($this->getUser(), $instrument);

        $terms = null;
        if ($instrument->hasTerms())
        {
            $terms = $this->entityManager->getRepository(InstrumentTerms::class)->latestTerms($instrument);
        }

        $last_price = $ip_service->latestPrice($instrument, $terms);

        $ap = $this->entityManager->getRepository(AssetPrice::class);
        $last_asset_price = $ap->latestPrice($instrument->getUnderlying());
        $leverage = $ip_service->computeLeverage($instrument, $last_asset_price, $terms);
        //var_dump($trades);

        $chartdatefrom = $last_price ? $last_price->getDate()->modify("-365 day") : null;

        $total = ['volume' => 0, 'costs' => 0, 'value' => 0, 'price' => null];
        $chart_open = [];
        $chart_close = [];
        foreach($trades as $trade)
        {
            $total['volume'] = $total['volume'] + $trade['direction'] * $trade['volume'];
            $total['costs'] = $total['costs'] + $trade['costs'];
            if ($trade['direction'] == 0) {
                // Dividends reduce the risk
                $total['value'] = $total['value'] - $trade['total'];
            } else {
                $total['value'] = $total['value'] + $trade['direction'] * $trade['total'];
                
                if ($trade['time'] >= $chartdatefrom)
                {
                    $tick = $trade['time']->getTimestamp() * 1000;
                    if ($trade['direction'] == 1)
                    {
                        $chart_open[] = ['x' => $tick, 'y' => $trade['price']];
                    }
                    else
                    {
                        $chart_close[] = ['x' => $tick, 'y' => $trade['price']];
                    }
                }
            }
        }
        if ($total['volume'] != 0)
        {
            $total['price'] = $total['value'] / $total['volume'];
        }

        return $this->render('instrument/show.html.twig', [
            'controller_name' => 'InstrumentController',
            'instrument' => $instrument,
            'trades' => $trades,
            'terms' => $terms,
            'total' => $total,
            'price' => $last_price,
            'chartdatefrom' => $chartdatefrom,
            'chart_open' => $chart_open,
            'chart_close' => $chart_close,
            'leverage' => $leverage,
        ]);
    }

    #[Route("/api/instrument/{id}", name: "instrument_delete", methods: ["DELETE"])]
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
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route("/api/instrument/terms/{id}", name: "instrument_terms_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function deleteTerms(InstrumentTerms $terms) {
        try
        {
            $this->entityManager->remove($terms);
            $this->entityManager->flush();
            $this->addFlash('success', "Instrument terms for {$terms->getInstrument()->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
