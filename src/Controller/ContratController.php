<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\StatutDemande;
use Symfony\Component\Mime\Email;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DemandeContratRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class ContratController extends AbstractController{
//   #[Route('/createContrat/{demandeId}', name: 'create_contrat', methods: ['POST'])]
// public function create(
//     int $demandeId,
//     EntityManagerInterface $em,
//     DemandeContratRepository $demandeRepo,
//     ContratRepository $contratRepo,Request $request
// ): JsonResponse {
//     $demande = $demandeRepo->find($demandeId);
//     if (!$demande || $demande->getStatut()->value !== 'accepter') {
//         return $this->json(['error' => 'Demande invalide'], 400);
//     }

//     $dateDebut = new \DateTime();
//     $dateFin = (clone $dateDebut)->modify('+1 year');
//     $lastContrat = $contratRepo->findOneBy([], ['id' => 'DESC']);
//      $newNum = str_pad((string) (($lastContrat ? (int) $lastContrat->getNumContrat() : 0) + 1), 3, '0', STR_PAD_LEFT);

//     $contrat = new Contrat();
//     $contrat->setMontant(450.0);
//     $contrat->setDateDebut($dateDebut);
//     $contrat->setDateFin($dateFin);
//     $contrat->setNumContrat($newNum);
//     $contrat->setDemandeContrat($demande);
//     $contrat->setStatutContrat(StatutDemande::EN_ATTENTE);
//     $contrat->updateVieContrat(); // Ajout ici

//     $demande->setIsGenere(true);

//     $em->persist($contrat);

//     $em->flush();

//     $client = $demande->getClient();

//     return $this->json([
//         'message' => 'Contrat généré avec succès',
//         'contrat' => [
//             'id' => $contrat->getId(),
//             'num' => $contrat->getNumContrat(),
//             'dateDebut' => $contrat->getDateDebut()->format('Y-m-d'),
//             'dateFin' => $contrat->getDateFin()->format('Y-m-d'),
//             'statut' => $contrat->getStatutContart()->value,

//         ],
//         'demande' => [
//             'id' => $demande->getId(),
//             'description' => $demande->getDescription(),
//             'statut' => $demande->getStatut()->value,
//             'isGenere' => $demande->isGenere(),
//         ],
//         'client' => [
//             'id' => $client->getId(),
//             'nom' => $client->getNom(),
//             'prenom' => $client->getPrenom(),
//             'entrepriset' => $client->getEntreprise(),
//             'adresse' => $client->getAdresse(),
//         ]
//     ]);
// }
#[Route('/createContrat/{demandeId}', name: 'create_contrat', methods: ['POST'])]
public function create(
    int $demandeId,
    EntityManagerInterface $em,
    DemandeContratRepository $demandeRepo,
    ContratRepository $contratRepo,
    Request $request,
    MailerInterface $mailer,
    LoggerInterface $logger
): JsonResponse {
    $demande = $demandeRepo->find($demandeId);
    if (!$demande || $demande->getStatut()->value !== 'accepter') {
        return $this->json(['error' => 'Demande invalide'], Response::HTTP_BAD_REQUEST);
    }

    $client = $demande->getClient();
    $dateDebut = new \DateTime();
    $dateFin = (clone $dateDebut)->modify('+1 year');
    $lastContrat = $contratRepo->findOneBy([], ['id' => 'DESC']);
    $newNum = str_pad((string) (($lastContrat ? (int) $lastContrat->getNumContrat() : 0) + 1), 3, '0', STR_PAD_LEFT);

    try {
        // Création du contrat
        $contrat = new Contrat();
        $contrat->setMontant(450.0)
                ->setDateDebut($dateDebut)
                ->setDateFin($dateFin)
                ->setNumContrat($newNum)
                ->setDemandeContrat($demande)
                ->setStatutContrat(StatutDemande::EN_ATTENTE)
                ->updateVieContrat();

        $demande->setIsGenere(true);

        $em->persist($contrat);

        // Création de la notification
        $notif = new Notification();
        $notif->setTitre('Votre contrat a été généré')
              ->setMessage(sprintf('Votre contrat %s a été créé avec succès', $newNum))
              ->setIsRead(false)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUsers($client);

        $em->persist($notif);

        // Envoi de l'email
        $email = (new Email())
            ->from('contrats@votresociete.com')
            ->to($client->getEmail())
            ->subject('Votre contrat a été généré')
            ->html($this->renderView('emails/nouveau_contrat.html.twig', [
                'client' => $client,
                'contrat' => $contrat,
                'demande' => $demande
            ]));

        $mailer->send($email);
        $logger->info('Email envoyé au client', ['email' => $client->getEmail()]);

        $em->flush();

        return $this->json([
            'message' => 'Contrat généré avec succès',
            'contrat' => [
                'id' => $contrat->getId(),
                'num' => $contrat->getNumContrat(),
                'dateDebut' => $contrat->getDateDebut()->format('Y-m-d'),
                'dateFin' => $contrat->getDateFin()->format('Y-m-d'),
                'statut' => $contrat->getStatutContart()->value,
            ],
            'demande' => [
                'id' => $demande->getId(),
                'description' => $demande->getDescription(),
                'statut' => $demande->getStatut()->value,
                'isGenere' => $demande->isGenere(),
            ],
            'client' => [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'entreprise' => $client->getEntreprise(),
                'adresse' => $client->getAdresse(),
            ]
        ], Response::HTTP_CREATED);

    } catch (\Exception $e) {
        $logger->error('Erreur création contrat', ['error' => $e->getMessage()]);
        return $this->json([
            'error' => 'Une erreur est survenue',
            'details' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
#[Route('/getContratByDemande/{demandeId}', name: 'get_contrat_by_demande', methods: ['GET'])]
public function getContratByDemande(
    int $demandeId,
    ContratRepository $contratRepo
): JsonResponse {
    $contrat = $contratRepo->findOneBy(['demandeContrat' => $demandeId]);

    if (!$contrat) {
        return $this->json(['message' => 'Aucun contrat trouvé'], 404);
    }

    $client = $contrat->getDemandeContrat()->getClient();

    return $this->json([
        'contrat' => [
            'id' => $contrat->getId(),
            'num' => $contrat->getNumContrat(),
            'dateDebut' => $contrat->getDateDebut()->format('Y-m-d'),
            'dateFin' => $contrat->getDateFin()->format('Y-m-d'),
            'contratNum' => $contrat ? $contrat->getNumContrat() : null,
            'statut' => $contrat->getStatutContart()->value,


        ],
        'demande' => [
            'id' => $contrat->getDemandeContrat()->getId(),
            'description' => $contrat->getDemandeContrat()->getDescription(),
            'isGenere' => $contrat->getDemandeContrat()->isGenere(),
            'contratNum' => $contrat->getNumContrat(),
        ],
        'client' => [
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'entreprise' => $client->getEntreprise(),
            'adresse' => $client->getAdresse(),
            'isGenere' => $contrat->getDemandeContrat()->isGenere(),
        ]
    ]);
}
#[Route('/acceptContrat/{id}', name: 'api_acceptContrat', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function acceptContrat(int $id, EntityManagerInterface $em,ContratRepository $contratRepository ): JsonResponse
{
    // Retrieve the demande by its ID
    $contrat = $contratRepository->find($id);

    if (!$contrat) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Accepted' (Assuming 'ACCEPTED' is a valid status in StatutDemande)
    try {
        $contrat->setStatutContrat(StatutDemande::Accepter);
       
        $em->persist($contrat);
        $em->flush();

        return $this->json(['message' => 'contrat accepted successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while accepting the contrat.'], 500);
    }
}

#[Route('/cancelContrat/{id}', name: 'api_cancelContart', methods: ['PUT'], requirements: ['id' => '\d+'])]
public function cancelContrat(int $id, EntityManagerInterface $em ,ContratRepository $ContratRepository): JsonResponse
{
    // Retrieve the demande by its ID
    $contart = $ContratRepository->find($id);

    if (!$contart) {
        return $this->json(['message' => 'Demande not found.'], 404);
    }

    // Change the status to 'Cancelled' (Assuming 'CANCELLED' is a valid status in StatutDemande)
    try {
        $statut = StatutDemande::ANNULEE; // Replace this with the correct enum value
        $contart->setStatutContrat($statut);
        

        $em->persist($contart);
        $em->flush();

        return $this->json(['message' => ' contart cancelled successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while cancelling the contrat.'], 500);
    }
}
#[Route('/getAllContrat', name: 'api_getAllContrat', methods: ['GET'])]
public function getAllContratss(ContratRepository $ContratRepository): JsonResponse
{
    $contrats = $ContratRepository->findAllContrat();

    if (empty($contrats)) {
        return $this->json([
            'status' => 'success',
            'message' => 'Aucune demande trouvée',
            'data' => []
        ]);
    }

    $contratData = array_map(function($contrat) {
        return [
            'id' => $contrat->getId(),
            'demandecontrat' => $contrat->getDemandeContrat()->getId(),
            'num' => $contrat->getNumContrat(),
            'dateDebut' => $contrat->getDateDebut()->format('Y-m-d'),
            'dateFin' => $contrat->getDateFin()->format('Y-m-d'),
            'montant' => $contrat->getMontant(),
            'statut' => $contrat->getStatutContart()->value,
            'vieContrat' => $contrat->getVieContart()->value,
            'demande' => [
                'id' => $contrat->getDemandeContrat()->getId(),
                'description' => $contrat->getDemandeContrat()->getDescription(),
                'statut' => $contrat->getDemandeContrat()->getStatut()->value,
                'isGenere' => $contrat->getDemandeContrat()->isGenere(),
            ],
            'client' => [
                'id' => $contrat->getDemandeContrat()->getClient()->getId(),
                'nom' => $contrat->getDemandeContrat()->getClient()->getNom(),
                'prenom' => $contrat->getDemandeContrat()->getClient()->getPrenom(),
                'entreprise' => $contrat->getDemandeContrat()->getClient()->getEntreprise(),
                'email' => $contrat->getDemandeContrat()->getClient()->getEmail()
            ]
            
        ];
    }, $contrats);

    return $this->json([
        'status' => 'success',
        'data' => $contratData
    ]);
}
    #[Route('/getContratByEmail', name: 'get_Contrat_by_email', methods: ['GET'])]
    public function getContratByEmail(
        Request $request,
        ContratRepository $ContratRepository
    ): JsonResponse {
        $email = $request->query->get('email');
        $role = $request->query->get('role'); // ROLE_CLIENT ou ROLE_TECHNICIEN
    
        if (!$email || !$role) {
            return $this->json(['status' => 'error', 'message' => 'Email et rôle requis.'], Response::HTTP_BAD_REQUEST);
        }
    
         try {
        $contrats = $ContratRepository->findContratsByEmail($email, $role);
    } catch (\InvalidArgumentException $e) {
        return $this->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], Response::HTTP_BAD_REQUEST);
    }

    // 4. Vérification si des contrats ont été trouvés
   if (empty($contrats)) {
        return $this->json([
            'status' => 'success',
            'message' => 'Aucun contrat trouvé pour cet email',
            'data' => []
        ]);
    }

    // 5. Formatage de la réponse
    $response = array_map(function($contrat) {
        $demande = $contrat->getDemandeContrat();
        $client = $demande->getClient();

       return [
            'id' => $contrat->getId(),
            'demandecontrat' => $contrat->getDemandeContrat()->getId(),
            'num' => $contrat->getNumContrat(),
            'dateDebut' => $contrat->getDateDebut()->format('Y-m-d'),
            'dateFin' => $contrat->getDateFin()->format('Y-m-d'),
            'montant' => $contrat->getMontant(),
            'statut' => $contrat->getStatutContart()->value,
            'vieContrat' => $contrat->getVieContart()->value,
            'demande' => [
                'id' => $contrat->getDemandeContrat()->getId(),
                'description' => $contrat->getDemandeContrat()->getDescription(),
                'statut' => $contrat->getDemandeContrat()->getStatut()->value,
                'isGenere' => $contrat->getDemandeContrat()->isGenere(),
            ],
            'client' => [
                'id' => $contrat->getDemandeContrat()->getClient()->getId(),
                'nom' => $contrat->getDemandeContrat()->getClient()->getNom(),
                'prenom' => $contrat->getDemandeContrat()->getClient()->getPrenom(),
                'entreprise' => $contrat->getDemandeContrat()->getClient()->getEntreprise(),
                'email' => $contrat->getDemandeContrat()->getClient()->getEmail()
            ]
        
        ];
    }, $contrats);

    // 6. Retour de la réponse
    return $this->json([
        'status' => 'success',
        'count' => count($response),
        'data' => $response
    ]);
    }}