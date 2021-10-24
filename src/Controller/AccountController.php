<?php

namespace App\Controller;

use App\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/accounts", name="account_list")
     */
    public function index(): Response
    {
        $accounts = $this->getDoctrine()
            ->getRepository(Account::class)
            ->findAll();
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'accounts' => $accounts
        ]);
    }

    /**
     * @Route("/accounts/new", name="account_new", methods={"GET", "POST"})
     */
    public function new(Request $request) {
        $account = new Account();
        return new Response("", Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
