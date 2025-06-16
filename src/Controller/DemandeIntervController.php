<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\StatutDemande;
use Symfony\Component\Mime\Email;
use App\Entity\DemandeIntervention;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeInterventionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\Loader\Configurator\validator;


#[Route('/api')]
final class DemandeIntervController extends AbstractController{
    private DemandeInterventionRepository $demandeRepository;
    private EntityManagerInterface $entityManager;
   private ValidatorInterface $validator;
    public function __construct(DemandeInterventionRepository $demandeRepository, EntityManagerInterface $entityManager,ValidatorInterface $validator)
    {
        $this->demandeRepository = $demandeRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
       
    }

#[Route('/saveDemande', name: 'api_saveDemande', methods: ['POST'])]
public function saveDemande(
    Request $request,
    DemandeInterventionRepository $demandeRepository,
    ClientRepository $clientRepository,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    LoggerInterface $logger

): JsonResponse {
    $data = json_decode($request->getContent(), true);
    
    // Vérifier les champs requis
    $donne = ['description', 'statut'];
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
    //     return $this->json(['message' => 'Client non trouvé.'], 404);
    // }

    // Vérification du répertoire de stockage
    $uploadDir = $this->getParameter('photos_directory');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Enregistrer la demande
    $em->beginTransaction();
    try {
        $demande = new DemandeIntervention();
        $demande->setDescription($data['description'])
                ->setStatut($statut)
                ->setDateDemande(new \DateTime())
                ->setActionDate(new \DateTime())
                ->setIsAffecter(false)
                ->setClient($client);

        // Gestion des photos en base64
        for ($i = 1; $i <= 3; $i++) {
            $photoKey = "photo$i";
            if (isset($data[$photoKey]) && !empty($data[$photoKey])) {
                $base64Image = $data[$photoKey];
                
                // Validation du format base64
                if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                    return $this->json(['message' => "Le format de la photo $i est invalide."], 400);
                }
                
                $imageType = $matches[1]; // jpg, png, etc.
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
                $imageData = base64_decode($base64Image);
                
                if ($imageData === false) {
                    return $this->json(['message' => "Erreur de décodage de la photo $i"], 400);
                }
                
                // Validation du type MIME
                $allowedTypes = ['jpeg', 'jpg', 'png'];
                if (!in_array(strtolower($imageType), $allowedTypes)) {
                    return $this->json(['message' => "La photo $i doit être au format JPEG ou PNG."], 400);
                }
                
                // Génération d'un nom de fichier unique
                $newFilename = 'demande_' . uniqid() . "_$i." . $imageType;
                $filePath = $uploadDir . '/' . $newFilename;
                
                // Sauvegarde du fichier
                if (file_put_contents($filePath, $imageData) === false) {
                    return $this->json(['message' => "Erreur lors de l'enregistrement de la photo $i"], 500);
                }
                
                // Enregistrer le nom de fichier dans l'entité
                $setter = "setPhoto$i";
                if (method_exists($demande, $setter)) {
                    $demande->$setter($newFilename);
                }
            }
        }

     
         $notif = new Notification();
        $notif->setTitre('Votre demande contrat a été généré')
              ->setMessage(sprintf('Votre demande contrat %s a été créé avec succès', $demande->getDescription()))
              ->setIsRead(false)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUsers($client);

      

        // Envoi de l'email
        $email = (new Email())
            ->from('contrats@votresociete.com')
            ->to('admin@gmail.com')
->subject('Confirmation de votre demande dIntervention #'.$demande->getId())
            ->html($this->renderView('emails/nouveau_demandeIntervention.html.twig', [
                'client' => $client,
                'demande' => $demande,
            ]));

        $mailer->send($email);
        $logger->info('Email envoyé au admin');

       
       $em->persist($demande);
         $em->persist($notif);
        $em->flush();
        $em->commit();

        return $this->json(['message' => 'Demande ajoutée avec succès'], 201);
    } catch (\Exception $e) {
        $em->rollback();
        return $this->json([
            'message' => 'Une erreur est survenue lors de l\'enregistrement.',
            'error' => $e->getMessage()
        ], 500);
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
        $baseUrl = $this->getParameter('app.base_url') . '/uploads/demandes/';

        // Map the demandes to an array of data
        $demandeData = [];
        foreach ($demandes as $demande) {
              $statutsAffectation = [];
        foreach ($demande->getAffecterDemandes() as $affectation) {
            $statutsAffectation[] = $affectation->getStatutAffectation();
        }

            $demandeData[] = [
                'id' => $demande->getId(),
                'description' => $demande->getDescription(),
                'statut' => $demande->getStatut(),// Enum value as string
                 
                'client' => [
                    'id' => $demande->getClient()->getId(),
                    'adresse'=>$demande->getClient()->getAdresse(),
                    'entreprise'=>$demande->getClient()->getEntreprise(),
                    'email' => $demande->getClient()->getEmail(),
                    'nom' => $demande->getClient()->getNom(),
                    'prenom' => $demande->getClient()->getPrenom(),
                ],
                'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s'),
                'actionDate' => $demande->getActionDate()->format('Y-m-d H:i:s'),
                'isAffecter' => $demande->isAffecter(),
                'photos' => [
                    $demande->getPhoto1() ? $baseUrl . $demande->getPhoto1() : null,
                    $demande->getPhoto2() ? $baseUrl . $demande->getPhoto2() : null,
                    $demande->getPhoto3() ? $baseUrl . $demande->getPhoto3() : null,
                ],
            'statutsAffectation' => $statutsAffectation,
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
        // $demandes= [ 'description', ];
        // foreach ($demandes as $demande) {
        //     if (!isset($data[$demande])) {
        //         return $this->json(['message' => "Le champ '$demande' est requis."], 400);
        //     }
        // }
    
        // Retrieve the DemandeIntervention entity by ID
        $demande = $this->demandeRepository->find($demandeId);
        // if (!$demande) {
        //     return $this->json(['message' => 'Demande non trouver.'], 404);
        // }
        // if (isset($data['description'])) {
        //     $demande->setDescription($data['description']);
        // }
        
    
    
        // Optionally update associated client data
        if (isset($data['client'])) {
            $client = $demande->getClient();
            // if (!$client) {
            //     return $this->json(['message' => 'Client associé non trouvé.'], 404);
            // }
    
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
                    //'statut' => $demande->getStatut()->value, // Accéder à la valeur de l'énumération
                    'client' => [
                        'id' => $demande->getClient()->getId(),
                        'entreprise' => $demande->getClient()->getEntreprise(),
                        'adresse' => $demande->getClient()->getAdresse(),
                    ],
                    'dateDemande' => $demande->getDateDemande()->format('Y-m-d H:i:s'),
                    'actionDate' => $demande->getActionDate() ? $demande->getActionDate()->format('Y-m-d H:i:s') : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage()], 500);
        }
    }
    #[Route('/deleteDemande/{id}', name: 'api_deleteDemande', methods: ['DELETE'])]
public function deleteDemande(DemandeInterventionRepository $demandeInterventionRepository, EntityManagerInterface $em, int $id): JsonResponse
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
        $demande->setActionDate(new \DateTime());

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
        $demande->setActionDate(new \DateTime());

        $em->persist($demande);
        $em->flush();

        return $this->json(['message' => 'Demande cancelled successfully.']);
    } catch (\Exception $e) {
        return $this->json(['message' => 'An error occurred while cancelling the demande.'], 500);
    }
}

// #[Route('/demandesclient', name: 'api_demandes_by_client_email', methods: ['GET'])]
// public function getDemandeByClientEmail(
//     Request $request,
//     SerializerInterface $serializer,
//     ClientRepository $clientRepository,
//     ValidatorInterface $validator
// ): JsonResponse {
//     // 1. Récupération et validation de l'email
//     $email = $request->query->get('email');
    
//     $errors = $validator->validate($email, [
//         new Assert\NotBlank(),
//         new Assert\Email()
//     ]);

//     if (count($errors) > 0) {
//         return $this->json([
//             'status' => 'error',
//             'message' => 'Email invalide',
//             'errors' => (string) $errors
//         ], JsonResponse::HTTP_BAD_REQUEST);
//     }

//     // 2. Vérification de l'existence du client
//     $client = $clientRepository->findOneBy(['email' => $email]);
    
//     if (!$client) {
//         return $this->json([
//             'status' => 'error',
//             'message' => 'Client non trouvé'
//         ], JsonResponse::HTTP_NOT_FOUND);
//     }

//     // 3. Récupération des demandes
//     $demandes = $this->demandeRepository->findBy([
//         'client' => $client
//     ], ['dateDemande' => 'DESC']);

//     // 4. Formatage de la réponse
//     $context = [
//         'groups' => ['demande:read'],
//         'datetime_format' => 'Y-m-d H:i:s'
//     ];

//     return new JsonResponse(
//         $serializer->serialize([
//             'status' => 'success',
//             'count' => count($demandes),
//             'data' => $demandes
//         ], 'json', $context),
//         JsonResponse::HTTP_OK,
//         [],
//         true
//     );
// }
}