<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PortfolioController extends AbstractController {
  /**
   * @Route("/", name="portfolio_list", methods={"GET"})
   */
  public function index(): Response {
    return $this->render('portfolio.html.twig', ['body' => 'Hello World']);
  }
}
