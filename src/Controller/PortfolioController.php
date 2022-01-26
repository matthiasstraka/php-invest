<?php
namespace App\Controller;

use App\Entity\Execution;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PortfolioController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/", name: "portfolio_list")]
    #[IsGranted("ROLE_USER")]
    public function index(): Response
    {
        $repo = $this->entityManager->getRepository(Execution::class);
        $portfolio_positions = $repo->getPositionsForUser($this->getUser());
        //var_dump($portfolio_positions);

        $total = ['totalvalue' => 0];
        foreach($portfolio_positions as $pos)
        {
            $total['totalvalue'] = $total['totalvalue'] + $pos['totalvalue'];
        }

        return $this->render('portfolio/index.html.twig', [
          'positions' => $portfolio_positions,
          'total' => $total,
        ]);
    }
}
