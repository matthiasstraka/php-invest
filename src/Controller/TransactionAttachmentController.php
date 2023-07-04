<?php
namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionAttachment;
use App\Form\TransactionAttachmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionAttachmentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/transactionattachment/{transaction}/show", name: "transactionattachment_show")]
    #[IsGranted("ROLE_USER")]
    public function show(Transaction $transaction, Request $request)
    {
        $account = $transaction->getAccount();
        if ($account->getOwner() != $this->getUser())
        {
            $this->addFlash('error', 'You do not own this account');
            return $this->redirectToRoute('portfolio_list');
        }

        $attachment = new TransactionAttachment($transaction);
        $form = $this->createForm(TransactionAttachmentType::class, $attachment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $content = $form->get('file')->getData();
            if ($content)
            {
                $filename = $content->getClientOriginalName();
                $mime = $content->getClientMimeType();
                
                $location = $content->getPathName();                
                $filesize = filesize($location);

                $f = fopen($location, 'rb');
                $content = fread($f, $filesize);
                fclose($f);

                $attachment->setContent($filename, $mime, $content);
                $this->entityManager->persist($attachment);
                $this->entityManager->flush();
                $this->addFlash('success', 'File ' . $attachment->getName() . ' uploaded');
            }
            else
            {
                $this->addFlash('error', 'Upload error');
            }
        }
        
        $attachments = $this->entityManager->getRepository(TransactionAttachment::class)->getTransactionAttachments($transaction);

        // make a clean form (TODO: is there a better way?)
        $attachment = new TransactionAttachment($transaction);
        $form = $this->createForm(TransactionAttachmentType::class, $attachment);

        return $this->render('transactionattachment/show.html.twig', [
            'controller_name' => 'TransactionAttachmentController',
            'transaction' => $transaction,
            'attachments' => $attachments,
            'upload' => $form,
        ]);
    }

    #[Route("/transactionattachment/{id}", name: "transactionattachment_download", methods: ["GET"])]
    #[IsGranted("ROLE_USER")]
    public function download(TransactionAttachment $attachment, Request $request) {
        if ($attachment->getTransaction()->getAccount()->getOwner() != $this->getUser())
        {
            return new Response('No access to account', Response::HTTP_FORBIDDEN);
        }

        $is_download = $request->query->get('download');
        $content = stream_get_contents($attachment->getContent());
        $header = [
            'Content-Type' => $attachment->getMimetype(),
            'Content-Length' => strlen($content),
            'Content-Disposition' => HeaderUtils::makeDisposition(
                $is_download ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
                $attachment->getName()),
        ];

        return new Response($content, Response::HTTP_OK, $header);
    }

    #[Route("/api/transactionattachment/{id}", name: "transactionattachment_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete(TransactionAttachment $attachment) {
        try
        {
            if ($attachment->getTransaction()->getAccount()->getOwner() != $this->getUser())
            {
                $this->addFlash('error', 'You do not own this account');
                return $this->redirectToRoute('account_list');
            }
            $this->entityManager->remove($attachment);
            $this->entityManager->flush();
            $this->addFlash('success', "Attachment deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
