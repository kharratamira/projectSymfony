<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\StatutDemande;
use App\Entity\DemandeContrat;
use Symfony\Component\Mime\Email;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\DemandeContratRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class DemandeContratController extends AbstractController{

   
    #[Route('/createDemandeContrat', name: 'create_demande_contrat', methods: ['POST'])]
    public function createDemandeContrat(Request $request, EntityManagerInterface $em, MailerInterface $mailer,
    LoggerInterface $logger): Response
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
                       ->setDateDemande(new \DateTime()) 
                       ->setDescription($description)
                       ->setDateAction(new \DateTime())
                       ->setIsGenere(false);
                        // Date actuelle
    
        $em->persist($demandeContrat);
         // Création de la notification
        $notif = new Notification();
        $notif->setTitre('Votre demande contrat a été généré')
              ->setMessage(sprintf('Votre demande contrat %s a été créé avec succès', $demandeContrat->getDescription()))
              ->setIsRead(false)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUsers($client);

        $em->persist($notif);

        // Envoi de l'email
        $email = (new Email())
            ->from('contrats@votresociete.com')
            ->to('admin@gmail.com')
->subject('Confirmation de votre demande de contrat #'.$demandeContrat->getId())
            ->html($this->renderView('emails/nouveau_demandecontrat.html.twig', [
                'client' => $client,
                'demande' => $demandeContrat,
            ]));

        $mailer->send($email);
        $logger->info('Email envoyé au client', ['email' => $client->getEmail()]);

        $em->flush();
       
    
        return $this->json([
            'success' => true,
            'message' => 'Demande de contrat créée avec succès.',
            'id' => $demandeContrat->getId(),
            'description' => $demandeContrat->getDescription(),
            'date_demande' => $demandeContrat->getDateDemande()->format('Y-m-d H:i:s'),
            'statut' => $demandeContrat->getStatut()->value,
            'isGenere' => $demandeContrat->isGenere(),
            

            'client' => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
            ],
        
        ]);
    }
   // Pour /getAllDemandesContrat
#[Route('/getAllDemandesContrat', name: 'api_getAllDemandesContrat', methods: ['GET'])]
public function getAllDemandesContrat(DemandeContratRepository $demandeContratRepository): JsonResponse
{
    $demandes = $demandeContratRepository->findAllDemande();

    if (empty($demandes)) {
        return $this->json([
            'status' => 'success',
            'message' => 'Aucune demande trouvée',
            'data' => []
        ]);
    }

    $demandeData = array_map(function($demande) {
        return [
            'id' => $demande->getId(),
            'description' => $demande->getDescription(),
            'statut' => $demande->getStatut()->value,
            
            'client' => [
                'id' => $demande->getClient()->getId(),
                'adresse' => $demande->getClient()->getAdresse(),
                'entreprise' => $demande->getClient()->getEntreprise(),
                'email' => $demande->getClient()->getEmail(),
                'nom' => $demande->getClient()->getNom(),
                'prenom' => $demande->getClient()->getPrenom(),

            ],
            'contrat' => $demande->getContrat() ? [
                'id' => $demande->getContrat()->getId(),
                'num' => $demande->getContrat()->getNumContrat(),
                'dateDebut' => $demande->getContrat()->getDateDebut()->format('Y-m-d'),
                'dateFin' => $demande->getContrat()->getDateFin()->format('Y-m-d'),
                'statut' => $demande->getContrat()->getStatutContart()?->value,
            ] : null,

            'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s'),
            'actionDate' => $demande->getDateAction() ? $demande->getDateAction()->format('Y-m-d H:i:s') : null,
            'isGenere' => $demande->isGenere(),
        ];
    }, $demandes);

    return $this->json([
        'status' => 'success',
        'data' => $demandeData
    ]);
}
    #[Route('/getDemandeContratByEmail', name: 'get_demandeContrat_by_email', methods: ['GET'])]
    public function getDemandeContratByEmail(
        Request $request,
        DemandeContratRepository $demandeContratRepository
    ): JsonResponse {
        $email = $request->query->get('email');
        $role = $request->query->get('role'); // ROLE_CLIENT ou ROLE_TECHNICIEN
    
        if (!$email || !$role) {
            return $this->json(['status' => 'error', 'message' => 'Email et rôle requis.'], Response::HTTP_BAD_REQUEST);
        }
    
        try {
            $demandes = $demandeContratRepository->findDemandeContratByEmail($email, $role);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    
        $response = [];
        foreach ($demandes as $demande) {
            $client = $demande->getClient();
    
            $response[] = [
                'id' => $demande->getId(),
                'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s'),
                'description' => $demande->getDescription(),
                'statut' => $demande->getStatut(),
                'action_date' => $demande->getDateAction()?->format('Y-m-d H:i:s'),
                'isGenere' => $demande->isGenere(),
                'client' => [
                    'entreprise' => $client->getEntreprise(),
                    'nom' => $client->getNom(),
                    'prenom' => $client->getPrenom(),
                    'email' => $client->getEmail(),
                    'adresse' => $client->getAdresse(), 
                ],
                'contrat' => $demande->getContrat() ? [
    'id' => $demande->getContrat()->getId(),
    'num' => $demande->getContrat()->getNumContrat(),
    'dateDebut' => $demande->getContrat()->getDateDebut()->format('Y-m-d'),
    'dateFin' => $demande->getContrat()->getDateFin()->format('Y-m-d'),
    'statut' => $demande->getContrat()->getStatutContart()?->value,
] : null,

            ];
        }
    
        return $this->json([
            'status' => 'success',
            'data' => $response
        ]);
    }
    #[Route('/updateDemandeContrat/{id}', name: 'update_demande_contrat', methods: ['PUT'])]
    public function updateDemandeContrat(
    int $id,
    Request $request,
    DemandeContratRepository $demandeContratRepository,
    EntityManagerInterface $entityManager
): JsonResponse {
    // Récupérer l'intervention par son ID
    $demandeContrat = $demandeContratRepository->find($id);

    if (!$demandeContrat) {
        return $this->json(['status' => 'error', 'message' => 'Demande Contrat non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Récupérer les données de la requête
    $data = json_decode($request->getContent(), true);

    

        
    // Mettre à jour l'observation
    if (isset($data['description'])) {
        $demandeContrat->setDescription($data['description']);
    }

    $entityManager->flush();

    return $this->json(['status' => 'success', 'message' => 'Intervention mise à jour avec succès.']);
}

#[Route('/acceptDemandecontrat/{id}', name: 'api_acceptDemandeContrat', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function acceptDemande(int $id, EntityManagerInterface $em,DemandeContratRepository $demandeContratRepository ): JsonResponse
{
    // Retrieve the demande by its ID
    $demande = $demandeContratRepository->find($id);

    if (!$demande) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Accepted' (Assuming 'ACCEPTED' is a valid status in StatutDemande)
    try {
        $statut = StatutDemande::Accepter; // Replace this with the correct enum value
        $demande->setStatut($statut);
        $demande->setDateAction(new \DateTime());

        $em->persist($demande);
        $em->flush();

        return $this->json(['message' => 'Demande contrat accepted successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while accepting the demande contrat.'], 500);
    }
}

#[Route('/cancelDemandeContrat/{id}', name: 'api_cancelDemandeContart', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function cancelDemande(int $id, EntityManagerInterface $em ,DemandeContratRepository $demandeContratRepository): JsonResponse
{
    // Retrieve the demande by its ID
    $demande = $demandeContratRepository->find($id);

    if (!$demande) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Cancelled' (Assuming 'CANCELLED' is a valid status in StatutDemande)
    try {
        $statut = StatutDemande::ANNULEE; // Replace this with the correct enum value
        $demande->setStatut($statut);
        $demande->setDateAction(new \DateTime());

        $em->persist($demande);
        $em->flush();

        return $this->json(['message' => 'Demande contart cancelled successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while cancelling the demande contrat.'], 500);
    }
}

}    