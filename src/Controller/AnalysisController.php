<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AnalysisController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/analysis", name: "analysis_index", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function index(Request $request) {
        $user = $this->getUser();

        // TODO: what if accounts have different currencies?

        $qb = $this->entityManager->createQueryBuilder();
        $q = $qb->select('YEAR(t.time) as year, a.currency currency,
            SUM(COALESCE(t.cash, 0)) as cash,
            SUM(COALESCE(t.tax, 0)) as tax,
            SUM(COALESCE(t.commission, 0)) as commission,
            SUM(COALESCE(t.interest, 0)) as interest,
            SUM(COALESCE(t.consolidation, 0)) as consolidation')
        ->from('App\Entity\Account', 'a')
        ->innerJoin('App\Entity\Transaction', 't', Join::ON, 'a.id = t.account')
        ->where('a.owner = :user')
        ->addGroupBy("year")
        ->addGroupBy("currency")
        ->setParameter('user', $user)
        ->getQuery();

        $data = $q->getResult();
        //dd($data);

        return $this->render('analysis/index.html.twig', ['data' => $data]);
    }
}
