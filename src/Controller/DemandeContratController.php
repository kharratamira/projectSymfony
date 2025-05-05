<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\DemandeContrat;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class DemandeContratController extends AbstractController{

    // #[Route('/createDemandeContrat', name: 'create_demande_contrat', methods: ['POST'])]
    // public function createDemandeContrat(
    //     Request $request,
    //     ClientRepository $clientRepository,
    //     EntityManagerInterface $entityManager
    // ): JsonResponse {
    //     // Récupérer l'ID du client connecté depuis la session ou le token
    //     $clientId = $request->getSession()->get('client_id'); // Assurez-vous que l'ID du client est stocké dans la session
    
    //     if (!$clientId) {
    //         return $this->json(['status' => 'error', 'message' => 'Client non connecté.'], JsonResponse::HTTP_UNAUTHORIZED);
    //     }
    
    //     // Récupérer le client par son ID
    //     $client = $clientRepository->find($clientId);
    
    //     if (!$client) {
    //         return $this->json(['status' => 'error', 'message' => 'Client introuvable.'], JsonResponse::HTTP_NOT_FOUND);
    //     }
    
    //     // Récupérer les données de la requête
    //     $data = json_decode($request->getContent(), true);
    
    //     if (!isset($data['description']) || empty($data['description'])) {
    //         return $this->json(['status' => 'error', 'message' => 'La description est requise.'], JsonResponse::HTTP_BAD_REQUEST);
    //     }
    
    //     // Créer une nouvelle demande de contrat
    //     $demandeContrat = new DemandeContrat();
    //     $demandeContrat->setDescription($data['description']);
    //     $demandeContrat->setDateDemande(new \DateTime()); // Date actuelle
    //     $demandeContrat->setClient($client);
    
    //     // Sauvegarder la demande de contrat
    //     $entityManager->persist($demandeContrat);
    //     $entityManager->flush();
    
    //     return $this->json([
    //         'status' => 'success',
    //         'message' => 'Demande de contrat créée avec succès.',
    //         'data' => [
    //             'id' => $demandeContrat->getId(),
    //             'description' => $demandeContrat->getDescription(),
    //             'date_demande' => $demandeContrat->getDateDemande()->format('Y-m-d H:i:s'),
    //             'client' => [
    //                 'id' => $client->getId(),
    //                 'nom' => $client->getNom(),
    //                 'prenom' => $client->getPrenom(),
    //             ],
    //         ],
    //     ]);
    #[Route('/createDemandeContrat', name: 'create_demande_contrat', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
    
    
        // Récupérer et valider les données de la requête
        $data = json_decode($request->getContent(), true);
        $clientId = $data['client_id'] ?? null;

        if (!$clientId) {
            return $this->json(['error' => 'Client non connecté'], 400);
        }

        // Récupérer le client depuis la BDD
        $client = $em->getRepository(Client::class)->find($clientId);

        if (!$client) {
            return $this->json(['error' => 'Client introuvable'], 404);
        }

        $description = $data['description'] ?? null;
    
        if (!$description) {
            return $this->json(['error' => 'La description est requise.'], 400);
        }
    
        // Créer la demande de contrat
        $demandeContrat = new DemandeContrat();
        $demandeContrat->setClient($client)
                       ->setDateDemande(new \DateTime()) // Date système
                       ->setDescription($description);
    
        $em->persist($demandeContrat);
        $em->flush();
    
        return $this->json([
            'success' => true,
            'message' => 'Demande de contrat créée avec succès.',
            'id' => $demandeContrat->getId(),
            'description' => $demandeContrat->getDescription(),
            'date_demande' => $demandeContrat->getDateDemande()->format('Y-m-d H:i:s'),
            'client' => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
            ],
        
        ]);
    }
}    