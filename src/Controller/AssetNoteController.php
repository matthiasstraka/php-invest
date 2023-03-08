<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetNote;
use App\Form\AssetNoteType;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class AssetNoteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/assetnote/new", name: "assetnote_new", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request) {
        $asset_id = $request->query->get('asset');
        if (is_null($asset_id) || !is_numeric($asset_id))
        {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Asset parameter missing or malformed");
        }
        $asset_id = intval($asset_id);
        
        $note = new AssetNote();
        $note->setDate(new \DateTime());
        $note->setAuthor($this->getUser());

        $asset = $this->entityManager->getRepository(Asset::class)->find($asset_id);
        if (is_null($asset))
        {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Asset not found");
        }
        $note->setAsset($asset);

        $form = $this->createForm(AssetNoteType::class, $note);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $asset = $form->getData();
            
            $this->entityManager->persist($asset);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('asset_show', ['id' => $note->getAsset()->getId()]);
        }
        
        return $this->render('assetnote/edit.html.twig', [
            'form' => $form,
            'asset' => $note->getAsset(),
        ]);
    }

    #[Route("/assetnote/{id}/edit", name: "assetnote_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_USER")]
    public function edit(AssetNote $note, Request $request) {
        $form = $this->createForm(AssetNoteType::class, $note);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();

            $this->entityManager->persist($note);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('asset_show', ['id' => $note->getAsset()->getId()]);
        }
        
        return $this->render('assetnote/edit.html.twig', [
            'form' => $form,
            'asset' => $note->getAsset(),
        ]);
    }

    #[Route("/api/assetnote/{id}", name: "assetnote", methods: ["GET"])]
    public function note(?AssetNote $note): JsonResponse
    {
        if (is_null($note))
        {
            return new JsonResponse(['message' => 'Asset note not found'], Response::HTTP_NOT_FOUND);
        }
        $text = $note->getText();
        $converter = new CommonMarkConverter();
        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'type' => $note->getTypeName(),
            'date' => $note->getDate()->format("Y-m-d"),
            'text' => $text ? (string)$converter->convertToHtml($text) : null,
            'url' => $note->getUrl(),
        ]);
    }
    
    #[Route("/api/assetnote/{id}", name: "assetnote_delete", methods: ["DELETE"])]
    #[IsGranted("ROLE_USER")]
    public function delete(AssetNote $note) {
        try
        {
            $this->entityManager->remove($note);
            $this->entityManager->flush();
            $this->addFlash('success', "Asset note '{$note->getTitle()}' deleted.");
            return new JsonResponse(['message' => 'ok']);
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
