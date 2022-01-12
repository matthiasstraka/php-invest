<?php
namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/transaction/new", name: "transaction_new")]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request, ?UserInterface $user) {
        $account_id = intval($request->query->get('account'));

        $account = $this->entityManager->getRepository(Account::class)->findOneBy(['id' => $account_id, 'owner' => $user->getId()]);

        if ($account == null)
        {
            return new Response('Invalid account', Response::HTTP_BAD_REQUEST);
        }

        $transaction = new Transaction();
        $transaction->setAccount($account);
        $transaction->setTime(new \DateTime());

        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->addFlash('success', "Transaction added.");

            return $this->redirectToRoute('account_transactions', ['id' => $account->getId()]);
        }

        return $this->renderForm('transaction/new.html.twig', ['form' => $form]);
    }

    #[Route("/transaction/{id}/edit", name: "transaction_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Transaction $transaction, Request $request, ?UserInterface $user) {
        $account = $transaction->getAccount();
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return $this->redirectToRoute('account_transactions', ['id' => $account->getId()]);
        }

        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->addFlash('success', 'Transaction edited.');

            return $this->redirectToRoute('account_transactions', ['id' => $account->getId()]);
        }

        return $this->renderForm('transaction/edit.html.twig', ['form' => $form]);
    }

    #[Route("/transaction/{id}", name: "transaction_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete(Transaction $trans) {
        try
        {
            // TODO: Check if this transaction belong to the user!
            $this->entityManager->remove($trans);
            $this->entityManager->flush();
            $this->addFlash('success', "Transaction deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
