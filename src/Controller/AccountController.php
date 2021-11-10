<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/accounts", name="account_list")
     * @IsGranted("ROLE_USER")
     */
    public function index(?UserInterface $user): Response
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Account::class);
        $accounts = $repo->findBy(['owner' => $user->getId()]);
        $account_balances = $repo->getBalances($user);
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

    /**
     * @Route("/account/new", name="account_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request, ?UserInterface $user): Response {
        $account = new Account();

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();
            $account->setOwner($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($account);
            $entityManager->flush();

            $this->addFlash('success', 'Account created.');

            return $this->redirectToRoute('account_list');
        }

        return $this->renderForm('account/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/account/edit/{id}", name="account_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($asset);
            $em->flush();

            $this->addFlash('success', 'Account edited.');

            return $this->redirectToRoute('account_list');
        }

        return $this->renderForm('asset/edit.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/account/{id}", name="account_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(Account $account, ?UserInterface $user) {
        if ($account->getOwner() != $user)
        {
            $this->addFlash('error', 'You do not own this account');
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }
        try
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($account);
            $entityManager->flush();
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
