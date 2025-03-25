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
    //#[Route('/saveClient', name: 'api_saveclient', methods: ['POST'])]
    // public function saveClient(Request $request ,ClientRepository $clientRepository):JsonResponse
    // {
    //     $data=json_decode($request->getContent(),true);
    //     $client=new Client();
    //     $client->setNom($data['nom']);
    //     $client->setPrenom($data['prenom']);
    //     $client->setEmail($data['email']);
    //     $client->setAdresse($data['adresse']);
    //     $client->setNumeroTelephone($data['numeroTelephone']);
    //     $clientRepository->add($client);
    //     return $this->json(['message'=>'client ajouté avec succés'],201);
    // }
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
    
    

    // Optionally update associated DemandeIntervention
    // if (isset($data['nomSociete'])) {
    //     // Assuming you have a relationship between Client and DemandeIntervention
    //     foreach ($client->getDemandeInterventions() as $demande) {
    //         $demande->setNomSociete($data['nomSociete']);
    //     }
    // }

    try {
        // Use EntityManager to persist changes
        $em->persist($client);  // Persist client entity

        // Optionally, persist changes to associated DemandeIntervention
        // foreach ($client->getDemandeInterventions() as $demande) {
        //     $em->persist($demande);  // Persist each associated demande
        // }

        // Flush to save changes to the database
        $em->flush();  

        return $this->json(['message' => 'Client and associated demandes updated successfully.'], 200);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred during the update process: ' . $e->getMessage()], 500);
    }
}
    // Delete a Client
     // Ensure this is at the top of the file

// #[Route('/deleteClient/{id}', name: 'api_deleteClient', methods: ['DELETE'])]
// public function deleteClient(ClientRepository $clientRepository, EntityManagerInterface $em, int $id): JsonResponse
// {
//     // Retrieve the client to delete
//     $client = $clientRepository->find($id);

//     if (!$client) {
//         return $this->json(['message' => 'Client not found'], Response::HTTP_NOT_FOUND);
//     }

//     // Remove the client using EntityManager
//     try {
//         $em->remove($client);  // Remove the client entity
//         $em->flush();  // Flush to commit the deletion to the database
//         return $this->json(['message' => 'Client deleted successfully']);
//     } catch (\Exception $e) {
//         return $this->json(['message' => 'An error occurred during the deletion process'], Response::HTTP_INTERNAL_SERVER_ERROR);
//     }
// }

}
   
    

