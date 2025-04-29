<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Technicien;
use App\Entity\AutorisationSortie;
use App\Entity\StatutAutorisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api')]
final class AutorisationSortieController extends AbstractController
{
    
    
    
    #[Route('/Createautorisation', name: 'create_autorisation_sortie', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
    
        // Récupérer l'ID du technicien depuis les données envoyées
        $technicienId = $data['id_technicien'] ?? null;
    
        if (!$technicienId) {
            return $this->json(['error' => 'ID du technicien manquant'], 400);
        }
    
        $technicien = $em->getRepository(User::class)->find($technicienId);
    
        if (!$technicien) {
            return $this->json(['error' => 'Technicien introuvable'], 404);
        }
    
        $autorisation = new AutorisationSortie();
        $autorisation->setTechnicien($technicien)
                     ->setDateDebut(new \DateTime($data['dateDebut']))
                     ->setDateFin(new \DateTime($data['dateFin']))
                     ->setRaison($data['raison']);
    
        $em->persist($autorisation);
        $em->flush();
    
        return $this->json(['success' => true]);
    }
  #[Route('/getAutorisation', name: 'get_autorisation_sortie', methods: ['GET'])]
    public function getAutorisation(
        EntityManagerInterface $em
    ): Response {
        // 1. Vérifier l'utilisateur connecté
        $user = $this->getUser();
        if (!$user instanceof Technicien) {
            return $this->json([
                'status' => 'error',
                'message' => 'Seuls les techniciens peuvent voir les autorisations'
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Récupérer les autorisations
        $autorisationRepository = $em->getRepository(AutorisationSortie::class);
        $autorisationList = $autorisationRepository->findBy(['technicien' => $user]);

        // 3. Formater la réponse
        $response = [];
        foreach ($autorisationList as $autorisation) {
            $response[] = [
                'id' => $autorisation->getId(),
                'dateDebut' => $autorisation->getDateDebut()->format('Y-m-d H:i:s'),
                'dateFin' => $autorisation->getDateFin()->format('Y-m-d H:i:s'),
                'raison' => $autorisation->getRaison(),
                'statut' => $autorisation->getStatut()->value,
            ];
        }

        return $this->json($response, Response::HTTP_OK);
    }
}