<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\StatutDemande;
use App\Entity\DemandeIntervention;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeInterventionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/api')]
final class DemandeIntervController extends AbstractController{
    private DemandeInterventionRepository $demandeRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(DemandeInterventionRepository $demandeRepository, EntityManagerInterface $entityManager)
    {
        $this->demandeRepository = $demandeRepository;
        $this->entityManager = $entityManager;
    }
    #[Route('/saveDemande', name: 'api_saveDemande', methods: ['POST'])]
    public function saveDemande(
        Request $request,
        DemandeInterventionRepository $demandeRepository,
        ClientRepository $clientRepository,
        
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
    
        // Vérifier les champs requis
        $donne = [  'description', 'statut'];
        foreach ($donne as $donnes) {
            if (!isset($data[$donnes])) {
                return $this->json(['message' => "Le champ '$donnes' est requis."], 400);
            }
        }
    
        // Valider le statut
        try {
            $statut = StatutDemande::from($data['statut']);
        } catch (\ValueError $e) {
            return $this->json(['message' => 'Statut invalide. Les valeurs autorisées sont : ' . implode(', ', StatutDemande::cases())], 400);
        }
    
        // Récupérer le client existant
         $client = $clientRepository->findOneBy(['email' => $data['email']]);
        // if (!$client) {
        //     return $this->json(['message' => 'Client non trouvé. Veuillez vous inscrire d\'abord.'], 404);
        // }
    
        // Enregistrer la demande
        $em->beginTransaction();
        try {
            $demande = new DemandeIntervention();
            $demande->setDescription($data['description'])
                    ->setStatut($statut)
                    ->setDateDemande(new \DateTime())
                    ->setClient($client);
    
            $em->persist($demande);
            $em->flush();
            $em->commit();
    
            return $this->json(['message' => 'Demande ajoutée avec succès'], 201);
        } catch (\Exception $e) {
            $em->rollback();
            return $this->json(['message' => 'Une erreur est survenue lors de l\'enregistrement.'], 500);
        }
    }
     #[Route('/getDemandes', name: 'api_getDemandes', methods: ['GET'])]
    public function getDemandes(DemandeInterventionRepository $demandeRepository): JsonResponse
    {
        // Fetch all demandes with client details
        $demandes = $demandeRepository->findAllDemande();

        // If there are no demandes
        if (empty($demandes)) {
            return $this->json(['message' => 'Aucune demande trouvée.'], 404);
        }

        // Map the demandes to an array of data
        $demandeData = [];
        foreach ($demandes as $demande) {
            $demandeData[] = [
                'id' => $demande->getId(),
                //'nomSociete' => $demande->getNomSociete(),
                'description' => $demande->getDescription(),
                'statut' => $demande->getStatut(),// Enum value as string
                 
                'client' => [
                    'id' => $demande->getClient()->getId(),
                    // 'prenom' => $demande->getClient()->getPrenom(),
                    // 'email' => $demande->getClient()->getEmail(),
                    'adresse'=>$demande->getClient()->getAdresse(),
                    'entreprise'=>$demande->getClient()->getEntreprise(),
                    // 'numeroTelephone' =>$demande->getClient()->getNumeroTelephone(),
                    
                    // Add other client details if needed
                ],
                'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($demandeData);
    }
    #[Route('/updateDemande/{demandeId}', name: 'api_update_demande', methods: ['PUT'], requirements: ['demandeId' => '\d+'])]
    public function updateDemande(
        Request $request,
        int $demandeId,
        DemandeInterventionRepository $demandeRepository,
    EntityManagerInterface $entityManager
    ): JsonResponse {
        // Retrieve the updated data from the request
        $data = json_decode($request->getContent(), true);
    
        // Check if the required fields are present
        $donnes = [ 'description', 'statut'];
        foreach ($donnes as $donne) {
            if (!isset($data[$donne])) {
                return $this->json(['message' => "Le champ '$donne' est requis."], 400);
            }
        }
    
        // Retrieve the DemandeIntervention entity by ID
        $demande = $this->demandeRepository->find($demandeId);
        if (!$demande) {
            return $this->json(['message' => 'Demande not found.'], 404);
        }
        if (isset($data['description'])) {
            $demande->setDescription($data['description']);
        }
        
    
        // Handle the statut update (ensure valid values)
        if (isset($data['statut'])) {
            try {
                $statut = StatutDemande::from($data['statut']); // Assuming StatutDemande is an enum
                $demande->setStatut($statut);
            } catch (\ValueError $e) {
                return new JsonResponse([
                    'error' => 'Statut invalide. Les valeurs autorisées sont : ' . implode(', ', StatutDemande::getValues())
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
    
        // Optionally update associated client data
        if (isset($data['client'])) {
            $client = $demande->getClient();
            if (!$client) {
                return $this->json(['message' => 'Client associé non trouvé.'], 404);
            }
    
            // Mettre à jour l'entreprise du client
            if (isset($data['client']['entreprise'])) {
                $client->setEntreprise($data['client']['entreprise']);
            }
    
            // Mettre à jour l'adresse du client
            if (isset($data['client']['adresse'])) {
                $client->setAdresse($data['client']['adresse']);
            }
    
            $entityManager->persist($client); // Persister les modifications du client
        }
    
        try {
            // Persister les modifications de la demande
            $entityManager->persist($demande);
            $entityManager->flush();
    
            // Retourner la réponse avec les données mises à jour
            return $this->json([
                'message' => 'Demande et client associé mis à jour avec succès.',
                'demande' => [
                    'id' => $demande->getId(),
                    'description' => $demande->getDescription(),
                    'statut' => $demande->getStatut()->value, // Accéder à la valeur de l'énumération
                    'client' => [
                        'id' => $demande->getClient()->getId(),
                        'entreprise' => $demande->getClient()->getEntreprise(),
                        'adresse' => $demande->getClient()->getAdresse(),
                    ],
                    'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage()], 500);
        }
    }
    #[Route('/deleteDemande/{id}', name: 'api_deleteDemande', methods: ['DELETE'])]
public function deleteClient(DemandeInterventionRepository $demandeInterventionRepository, EntityManagerInterface $em, int $id): JsonResponse
{
    // Retrieve the client to delete
    $demande = $demandeInterventionRepository->find($id);

    if (!$demande) {
        return $this->json(['message' => 'demande not found'], Response::HTTP_NOT_FOUND);
    }

    // Remove the client using EntityManager
    try {
        $em->remove($demande);  // Remove the client entity
        $em->flush();  // Flush to commit the deletion to the database
        return $this->json(['message' => 'demande deleted successfully']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred during the deletion process'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
#[Route('/acceptDemande/{id}', name: 'api_acceptDemande', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function acceptDemande(int $id, EntityManagerInterface $em): JsonResponse
{
    // Retrieve the demande by its ID
    $demande = $this->demandeRepository->find($id);

    if (!$demande) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Accepted' (Assuming 'ACCEPTED' is a valid status in StatutDemande)
    try {
        $statut = StatutDemande::Accepter; // Replace this with the correct enum value
        $demande->setStatut($statut);

        $em->persist($demande);
        $em->flush();

        return $this->json(['message' => 'Demande accepted successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while accepting the demande.'], 500);
    }
}

#[Route('/cancelDemande/{id}', name: 'api_cancelDemande', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function cancelDemande(int $id, EntityManagerInterface $em): JsonResponse
{
    // Retrieve the demande by its ID
    $demande = $this->demandeRepository->find($id);

    if (!$demande) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Cancelled' (Assuming 'CANCELLED' is a valid status in StatutDemande)
    try {
        $statut = StatutDemande::ANNULEE; // Replace this with the correct enum value
        $demande->setStatut($statut);

        $em->persist($demande);
        $em->flush();

        return $this->json(['message' => 'Demande cancelled successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while cancelling the demande.'], 500);
    }
}

}    