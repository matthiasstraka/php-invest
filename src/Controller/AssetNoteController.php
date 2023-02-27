<?php

namespace App\Controller;

use App\Entity\AssetNote;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AssetNoteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/assetnote/{id}", name: "assetnote", methods: ["GET"])]
    public function note(AssetNote $note): JsonResponse
    {
        $converter = new CommonMarkConverter();
        return new JsonResponse([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'type' => $note->getTypeName(),
            'date' => $note->getDate(),
            'text' => (string)$converter->convertToHtml($note->getText()),
            'url' => $note->getUrl(),
        ]);
    }

    #[Route("/assetnote/{id}", name: "assetnote_delete", methods: ["DELETE"])]
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
            return new JsonResponse(['message' => $e->getMessage()], 409);
        }
    }
}
