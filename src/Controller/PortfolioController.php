<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PortfolioController extends AbstractController {
  /**
   * @Route("/", methods={"GET"})
   */
  public function index() {
    return $this->render('portfolio.html.twig', ['body' => 'Hello World']);
  }
}
