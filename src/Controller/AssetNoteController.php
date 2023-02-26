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

    #[Route("/assetnote/{id}", name: "assetnote")]
    public function note(AssetNote $note): JsonResponse
    {
        $converter = new CommonMarkConverter();
        return new JsonResponse([
            'title' => $note->getTitle(),
            'text' => (string)$converter->convertToHtml($note->getText()),
            'url' => $note->getUrl(),
        ]);
    }
}
