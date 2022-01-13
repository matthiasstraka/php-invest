<?php
namespace App\Controller;

use App\Entity\Execution;
use App\Entity\Instrument;
use App\Entity\Transaction;
use App\Form\ExecutionType;
use App\Form\Model\ExecutionFormModel;
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
        $instrument_id = intval($request->query->get('instrument'));
        $direction = $request->query->get('direction');

        $repo = $this->entityManager->getRepository(Execution::class);

        $data = new ExecutionFormModel();

        if ($instrument_id > 0)
        {
            $data->instrument = $this->entityManager->getRepository(Instrument::class)->find($instrument_id);
        }

        switch ($direction)
        {
            case "open": 
                $data->direction = 1;
                break;
            case "close": 
                $data->direction = -1;
                break;
            default:
                $data->direction = 0;
                $data->type = Execution::TYPE_DIVIDEND;
                break;
        }

        $data->time = new \DateTime();

        $form = $this->createForm(ExecutionType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $execution = new Execution();
            $execution->setTransaction(new Transaction());
            
            $data = $form->getData();
            $data->populateExecution($execution);

            $this->entityManager->persist($execution);
            $this->entityManager->flush();

            $this->addFlash('success', "Execution added");

            return $this->redirectToRoute('instrument_show', ["id" => $data->instrument->getId()]);
        }
        
        return $this->renderForm('execution/edit.html.twig', ['form' => $form]);
    }

    #[Route("/execution/{id}", name: "execution_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, ?Execution $execution) {
        $transaction = $execution->getTransaction();

        $data = new ExecutionFormModel();
        $data->fromExecution($execution);

        $form = $this->createForm(ExecutionType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->populateExecution($execution);

            $this->entityManager->persist($execution);
            $this->entityManager->flush();

            $this->addFlash('success', "Execution edited");

            return $this->redirectToRoute('instrument_show', ["id" => $data->instrument->getId()]);
        }

        return $this->renderForm('execution/edit.html.twig', ['form' => $form]);
    }
}
