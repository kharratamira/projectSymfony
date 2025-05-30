<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Technicien;
use App\Entity\Notification;
use Symfony\Component\Mime\Email;
use App\Entity\AutorisationSortie;
use App\Entity\StatutAutorisation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AutorisationSortieRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api')]
final class AutorisationSortieController extends AbstractController
{
    
    
    
    #[Route('/Createautorisation', name: 'create_autorisation_sortie', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em,UserRepository $userRepository, MailerInterface $mailer,): Response
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
    
        
           // Création de la notification
        $notif = new Notification();
        $notif->setTitre('Nouvelle autorisation de sortie')
              ->setMessage(sprintf(
                  'Une nouvelle autorisation a été créée pour %s %s',
                  $technicien->getPrenom(),
                  $technicien->getNom()
              ))
              ->setIsRead(false)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUsers($technicien);

               $notif = new Notification();
           $notif->setTitre('Bienvenue !');
           $notif->setMessage('Nouvelle autorisation de sortie .');
           $notif->setIsRead(isRead: false);
           $notif->setCreatedAt(new \DateTimeImmutable());
           $notif->setUsers($technicien);
           $em->persist($notif);
           $email = (new Email())
    ->from('amirakharrat541@gmail.com')
->to('admin@gmail.com')
            ->subject('Bienvenue sur notre plateforme !')
      ->html(sprintf(
        '<!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .highlight { color: #0066cc; font-weight: bold; }
            </style>
        </head>
        <body>
            <p>Bonjour Administrateur,</p>
            
            <p>Une nouvelle demande d\'autorisation a été soumise :</p>
            
            <ul>
                <li><strong>Technicien :</strong> <span class="highlight">%s %s</span></li>
                <li><strong>Période :</strong> Du %s au %s</li>
                <li><strong>Raison :</strong> %s</li>
            </ul>
            
            <p>Veuillez traiter cette demande dans les plus brefs délais.</p>
            
            <p>Cordialement,<br>Le service technique</p>
        </body>
        </html>',
        htmlspecialchars($technicien->getPrenom()),
        htmlspecialchars($technicien->getNom()),
        $autorisation->getDateDebut()->format('d/m/Y H:i'),
        $autorisation->getDateFin()->format('d/m/Y H:i'),
        htmlspecialchars($autorisation->getRaison())
    ));


       try {
        $mailer->send($email);  // Send the email
       } catch (\Exception $e) {
           // Handle any mail sending error here
           return new JsonResponse(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
       }

       // Final response
       $em->persist($autorisation);
       $em->persist($notif);
       $em->flush();
        return $this->json(['success' => true]);
    }
    
    
    #[Route('/getAutorisationByEmail', name: 'get_autorisation_sortie_by_email', methods: ['GET'])]
    public function getAutorisationByEmail(Request $request, EntityManagerInterface $em): Response
    {
        $email = $request->query->get('email');
    
        if (!$email) {
            return $this->json(['status' => 'error', 'message' => 'Email requis'], Response::HTTP_BAD_REQUEST);
        }
    
        $technicien = $em->getRepository(Technicien::class)->findOneBy(['email' => $email]);
    
        if (!$technicien) {
            return $this->json(['status' => 'error', 'message' => 'Technicien non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $autorisationRepository = $em->getRepository(AutorisationSortie::class);
        $autorisationList = $autorisationRepository->createQueryBuilder('a')
        ->where('a.technicien = :technicien')
        ->setParameter('technicien', $technicien)
        ->orderBy('a.id', 'DESC') // 🔥 Trier par dateDebut en ordre décroissant
        ->getQuery()
        ->getResult();
    
        $response = [];
        foreach ($autorisationList as $autorisation) {
            $response[] = [
                'id' => $autorisation->getId(),
                'dateDebut' => $autorisation->getDateDebut()->format('Y-m-d H:i:s'),
                'dateFin' => $autorisation->getDateFin()->format('Y-m-d H:i:s'),
                'raison' => $autorisation->getRaison(),
                'statut' => $autorisation->getStatutAutorisation()->value,
            ];
        }
    
        return $this->json([
            'status' => 'success',
            'data' => $response
        ]);
    }
    
#[Route('/getAllAutorisations', name: 'get_all_autorisations', methods: ['GET'])]
public function getAllAutorisations(EntityManagerInterface $em): Response
{
    // Récupérer toutes les autorisations
    $autorisationRepository = $em->getRepository(AutorisationSortie::class);
    $autorisationList = $autorisationRepository->createQueryBuilder('a')
    ->orderBy('a.id', 'DESC') // 🔥 Trier par dateDebut en ordre croissant
    ->getQuery()
    ->getResult();
    // Vérifier si des autorisations existent
    if (empty($autorisationList)) {
        return $this->json([
            'status' => 'success',
            'message' => 'Aucune autorisation trouvée.',
            'data' => []
        ], Response::HTTP_OK);
    }

    // Formater les autorisations pour la réponse JSON
    $response = [];
    foreach ($autorisationList as $autorisation) {
        $response[] = [
            'id' => $autorisation->getId(),
            'technicien' => [
                'id' => $autorisation->getTechnicien()->getId(),
                'nom' => $autorisation->getTechnicien()->getNom(),
                'prenom' => $autorisation->getTechnicien()->getPrenom(),
            ],
            'dateDebut' => $autorisation->getDateDebut()->format('Y-m-d H:i:s'),
            'dateFin' => $autorisation->getDateFin()->format('Y-m-d H:i:s'),
            'raison' => $autorisation->getRaison(),
            'statut' => $autorisation->getStatutAutorisation()->value,
        ];
    }

    // Retourner la réponse JSON
    return $this->json([
        'status' => 'success',
        'message' => 'Toutes les autorisations récupérées avec succès.',
        'data' => $response
    ], Response::HTTP_OK);
}
#[Route('/updateAutorisation/{id}', name: 'update_autorisation_sortie', methods: ['PUT'])]
public function updateAutorisation(
    int $id,
    Request $request,
    AutorisationSortieRepository $autorisationSortieRepository,
    EntityManagerInterface $em
): Response {
    $autorisation = $autorisationSortieRepository->find($id);

    if (!$autorisation) {
        return $this->json(['status' => 'error', 'message' => 'Autorisation non trouvée.'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);

    if (isset($data['dateDebut'])) {
        $autorisation->setDateDebut(new \DateTime($data['dateDebut']));
    }
    if (isset($data['dateFin'])) {
        $autorisation->setDateFin(new \DateTime($data['dateFin']));
    }
    if (isset($data['raison'])) {
        $autorisation->setRaison($data['raison']);
    }
    if (isset($data['statut'])) {
        $statut = StatutAutorisation::tryFrom($data['statut']);
        if ($statut) {
            $autorisation->setStatutAutorisation($statut);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Statut invalide.'], Response::HTTP_BAD_REQUEST);
        }
    }

    $em->flush();

    return $this->json(['status' => 'success', 'message' => 'Autorisation mise à jour avec succès.'], Response::HTTP_OK);
}

#[Route('/deleteAutorisation/{id}', name: 'delete_autorisation_sortie', methods: ['DELETE'])]
public function deleteAutorisation(
    int $id,
    AutorisationSortieRepository $autorisationSortieRepository,
    EntityManagerInterface $em
): Response {
    $autorisation = $autorisationSortieRepository->find($id);

    if (!$autorisation) {
        return $this->json(['status' => 'error', 'message' => 'Autorisation non trouvée.'], Response::HTTP_NOT_FOUND);
    }

    $em->remove($autorisation);
    $em->flush();

    return $this->json(['status' => 'success', 'message' => 'Autorisation supprimée avec succès.'], Response::HTTP_OK);
}
#[Route('/accepterAutorisation/{id}', name: 'accepter_autorisation_sortie', methods: ['PUT'])]
public function accepterAutorisation(
    int $id,
    AutorisationSortieRepository $autorisationSortieRepository,
    EntityManagerInterface $em
): Response {
    $autorisation = $autorisationSortieRepository->find($id);

    if (!$autorisation) {
        return $this->json(['status' => 'error', 'message' => 'Autorisation non trouvée.'], Response::HTTP_NOT_FOUND);
    }

    $autorisation->setStatutAutorisation(StatutAutorisation::ACCEPTER);
    $em->flush();

    return $this->json(['status' => 'success', 'message' => 'Autorisation acceptée avec succès.'], Response::HTTP_OK);
}

#[Route('/annulerAutorisation/{id}', name: 'annuler_autorisation_sortie', methods: ['PUT'])]
public function annulerAutorisation(
    int $id,
    AutorisationSortieRepository $autorisationSortieRepository,
    EntityManagerInterface $em
): Response {
    $autorisation = $autorisationSortieRepository->find($id);

    if (!$autorisation) {
        return $this->json(['status' => 'error', 'message' => 'Autorisation non trouvée.'], Response::HTTP_NOT_FOUND);
    }

    $autorisation->setStatutAutorisation(StatutAutorisation::ANNULEE);
    $em->flush();

    return $this->json(['status' => 'success', 'message' => 'Autorisation annulée avec succès.'], Response::HTTP_OK);
}
}
