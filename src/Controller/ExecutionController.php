<?php
namespace App\Controller;

use App\Entity\Account;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExecutionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/execution/new", name: "execution_new")]
    #[IsGranted("ROLE_USER")]
    public function newExecution(Request $request): Response
    {
        $instrument_id = intval($request->query->get('instrument'));
        $direction = $request->query->get('direction');
        $account = $request->query->get('account');

        $data = new ExecutionFormModel();

        if ($instrument_id > 0)
        {
            $data->instrument = $this->entityManager->getRepository(Instrument::class)->find($instrument_id);
        }

        if ($account > 0)
        {
            $data->account = $this->entityManager->getRepository(Account::class)->find($account);
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

            return $this->redirectToRoute('instrument_show', ["id" => $data->instrument->getId()]);
        }
        
        return $this->renderForm('execution/edit.html.twig', ['form' => $form]);
    }

    #[Route("/execution/edit/{id}", name: "execution_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, ?Execution $execution) {
        $data = new ExecutionFormModel();
        $data->fromExecution($execution);

        $form = $this->createForm(ExecutionType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->populateExecution($execution);

            $this->entityManager->persist($execution);
            $this->entityManager->flush();

            $redirect = $request->request->get('referer');
            if ($redirect) {
                return $this->redirect($redirect);
            } else {
                return $this->redirectToRoute('instrument_show', ["id" => $data->instrument->getId()]);
            }
        }

        return $this->renderForm('execution/edit.html.twig', ['form' => $form]);
    }
}
