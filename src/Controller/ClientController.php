<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface; 
#[Route('/api')]
 class ClientController extends AbstractController{
    
    #[Route('/listeClient', name: 'api_listeClient', methods: ['GET'])]
    public function getClientsWithDemande(ClientRepository $clientRepository):JsonResponse{
        $clients=$clientRepository->findAllClientWithDemande();
        return$this->json($clients);
    }
    #[Route('/updateClient/{clientId}', name: 'api_update_client', methods: ['PUT'], requirements: ['clientId' => '\d+'])]
public function updateClient(
    Request $request, 
    ClientRepository $clientRepository,
    EntityManagerInterface $em, // Inject EntityManagerInterface here
    int $clientId
): JsonResponse {
    // Get the updated data from the request
    $data = json_decode($request->getContent(), true);

    // Check if the required fields are present
    $donnes = ['nom', 'prenom', 'numTel', 'email','entreprise', 'adresse' ];
    foreach ($donnes as $donne) {
        if (!isset($data[$donne])) {
            return $this->json(['message' => "Le champ '$donne' est requis."], 400);
        }
    }

    // Retrieve the client by ID
    $client = $clientRepository->find($clientId);
    if (!$client) {
        return $this->json(['message' => 'Client not found.'], 404);
    }

    // Update client fields
    $client->setNom($data['nom']);
    $client->setPrenom($data['prenom']);
    $client->setNumTel($data['numTel']);
    $client->setEmail($data['email']);
    $client->setEntreprise($data['entreprise']);
    $client->setAdresse($data['adresse']);
    try {
        $em->persist($client);  
        $em->flush();  

        return $this->json(['message' => 'Client and associated demandes updated successfully.'], 200);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred during the update process: ' . $e->getMessage()], 500);
    }
}
   
}
   
    

