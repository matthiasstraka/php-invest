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
        }

        $data->time = new \DateTime();

        $form = $this->createForm(ExecutionType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $transaction = new Transaction();
            $transaction->setTime($data->time);
            $transaction->setAccount($data->account);
            $transaction->setInstrument($data->instrument);
            $transaction->setPortfolio(-1 * $data->direction * $data->amount * $data->price); // TODO: Currency conversion
            $transaction->setCommission(-1 * $data->commission);
            $transaction->setTax(-1 * $data->tax);
            $transaction->setInterest(-1 * $data->interest);
            $transaction->setNotes($data->notes);
            $this->entityManager->persist($transaction);

            $execution = new Execution();
            $execution->setAmount($data->amount);
            $execution->setPrice($data->price);
            $execution->setDirection($data->direction);
            $execution->setType($data->type);
            $execution->setTransaction($transaction);
            $this->entityManager->persist($execution);

            $this->entityManager->flush();

            $this->addFlash('success', "Execution added.");

            return $this->redirectToRoute('portfolio_list');
        }
        
        $params = ['form' => $form];
        //var_dump($params);
        return $this->renderForm('execution/new.html.twig', $params);
    }
}
