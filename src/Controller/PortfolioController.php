<?php
namespace App\Controller;

use App\Entity\Execution;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class PortfolioController extends AbstractController {
  /**
   * @Route("/", name="portfolio_list", methods={"GET"})
   * @IsGranted("ROLE_USER")
   */
  public function index(?UserInterface $user): Response
  {
      $doctrine = $this->getDoctrine();
      $repo = $doctrine->getRepository(Execution::class);
      $portfolio_positions = $repo->getPositionsForUser($user);
      //var_dump($portfolio_positions);
      return $this->render('portfolio/index.html.twig', [
        'positions' => $portfolio_positions,
      ]);
  }
}
