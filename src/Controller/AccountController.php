<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Entity\Execution;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/accounts", name: "account_list")]
    #[IsGranted("ROLE_USER")]
    public function index(?UserInterface $user): Response
    {
        $repo = $this->entityManager->getRepository(Account::class);
        $accounts = $repo->findBy(['owner' => $user->getId()]);
        $account_balances = $repo->getBalancesForUser($user);
        $account_balance = [];
        foreach ($account_balances as $b)
        {
            $account_balance[$b['id']] = $b['balance'];
        }
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'accounts' => $accounts,
            'account_balance' => $account_balance,
        ]);
    }

    #[Route("/account/new", name: "account_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request, ?UserInterface $user): Response {
        $account = new Account();

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();
            $account->setOwner($user);

            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $this->addFlash('success', 'Account created.');

            return $this->redirectToRoute('account_list');
        }

        return $this->renderForm('account/edit.html.twig', ['form' => $form]);
    }

    #[Route("/account/{id}/edit", name: "account_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(Account $account, Request $request, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return $this->redirectToRoute('account_list');
        }

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();

            $this->entityManager->persist($asset);
            $this->entityManager->flush();

            $this->addFlash('success', 'Account edited.');

            return $this->redirectToRoute('account_list');
        }

        return $this->renderForm('account/edit.html.twig', ['form' => $form]);
    }

    #[Route("/account/{id}/positions", name: "account_positions", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function positions(Account $account, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $repo_transaction = $this->entityManager->getRepository(Transaction::class);
        $repo_execution = $this->entityManager->getRepository(Execution::class);

        $account_positions = $repo_execution->getPositionsForAccount($account);
        $balance = $repo_transaction->getAccountBalance($account);
        
        return $this->render('account/positions.html.twig', [
            'account' => $account,
            'positions' => $account_positions,
            'balance' => $balance,
          ]);
    }

    #[Route("/account/{id}/transactions", name: "account_transactions", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function transactions(Account $account, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $repo_transaction = $this->entityManager->getRepository(Transaction::class);

        $account_transactions = $repo_transaction->getAccountTransactions($account);
        $balance = $repo_transaction->getAccountBalance($account);
        
        return $this->render('account/transactions.html.twig', [
            'account' => $account,
            'transactions' => $account_transactions,
            'balance' => $balance,
          ]);
    }

    #[Route("/account/{id}/trades", name: "account_trades", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function trades(Account $account, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $repo = $this->entityManager->getRepository(Execution::class);
        $account_trades = $repo->getAccountTrades($account);

        $repo_transaction = $this->entityManager->getRepository(Transaction::class);
        $balance = $repo_transaction->getAccountBalance($account);
        
        return $this->render('account/trades.html.twig', [
            'account' => $account,
            'trades' => $account_trades,
            'balance' => $balance,
          ]);
    }

    #[Route("/account/{id}", name: "account_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete(Account $account, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }
        try
        {
            $this->entityManager->remove($account);
            $this->entityManager->flush();
            $this->addFlash('success', "Account {$account->getName()} deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
