<?php

namespace App\Controller;

use App\Repository\TacheRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class TacheController extends AbstractController{
    #[Route('/taches', name: 'get_taches', methods: ['GET'])]
    public function getTaches(TacheRepository $tacheRepository): JsonResponse
    {
        $taches = $tacheRepository->findAllTaches();

        return $this->json([
            'status' => 'success',
            'data' => $taches
        ]);
    }
}
