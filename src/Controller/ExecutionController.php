<?php
namespace App\Controller;

use App\Entity\Execution;
use App\Entity\Instrument;
use App\Form\ExecutionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class ExecutionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/execution/new", name: "execution_new")]
    #[IsGranted("ROLE_USER")]
    public function newExecution(Request $request, ?UserInterface $user): Response
    {
        $repo = $this->entityManager->getRepository(Execution::class);

        $execution = new Execution();

        $instrument_id = intval($request->query->get('instrument'));
        $instrument = null;
        if ($instrument_id > 0)
        {
            $instrument = $this->entityManager->getRepository(Instrument::class)->find($instrument_id);
            $execution->setInstrument($instrument);
        }

        $direction = $request->query->get('direction');
        if ($direction == 'buy' || $direction == 'sell')
        {
            $execution->setDirection($direction == 'buy' ? 1 : -1);
        }
      
        $execution->setTime(new \DateTime());

        $form = $this->createForm(ExecutionType::class, $execution);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $execution = $form->getData();

            $this->entityManager->persist($execution);
            $this->entityManager->flush();

            $this->addFlash('success', "Execution added.");

            return $this->redirectToRoute('portfolio_list');
        }
        
        $params = [];
        $params['instrument'] = $instrument;
        $params['form'] = $form;
        //var_dump($params);
        return $this->renderForm('execution/new.html.twig', $params);
    }
}
