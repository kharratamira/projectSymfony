<?php

namespace App\Controller;

use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\AffecterDemande;
use App\Entity\StatutAffectation;
use Symfony\Component\Mime\Email;
use App\Entity\StatutAutorisation;
use App\Repository\TechnicienRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffecterDemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AutorisationSortieRepository;
use App\Repository\DemandeInterventionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class AffectationController extends AbstractController{

    #[Route('/createAffectation', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        DemandeInterventionRepository $demandeRepository,
        TechnicienRepository $technicienRepository,
        AffecterDemandeRepository $affectationRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
        , AutorisationSortieRepository $autorisationSortieRepository,
         MailerInterface $mailer,
    LoggerInterface $logger
    ): JsonResponse {
        try {
            // 1. Décoder les données JSON
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
    
            // 2. Validation des champs requis
            $donnes = ['demande_id', 'technicien_id', 'date_prevu'];
            foreach ($donnes as $donne) {
                if (!isset($data[$donne]) || empty($data[$donne])) {
                    return $this->json(
                        ['error' => sprintf('Le champ "%s" est requis.', $donne)],
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }
    
            // 3. Récupération des entités
            $demande = $demandeRepository->find($data['demande_id']);
           
            $technicien = $technicienRepository->find($data['technicien_id']);

            
            if ($demande->getStatut()->value !== 'accepter') {
                return $this->json(
                    ['error' => 'La demande doit être dans un état accepté pour être affectée.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $datePrevu = new \DateTime($data['date_prevu']);
            $currentDate = new \DateTime();
             $dayOfWeek = (int) $datePrevu->format('w');
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return $this->json([
                'error' => 'Il est impossible d’affecter un technicien un samedi ou un dimanche.'
            ], Response::HTTP_BAD_REQUEST);
        }
            if ($datePrevu < $currentDate) {
                return $this->json(
                    ['error' => 'La date prévue doit être égale ou postérieure à la date actuelle.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $hour = (int)$datePrevu->format('H');
        if ($hour < 8 || $hour >= 17) {
            return $this->json([
                'error' => 'L’heure de l’affectation doit être entre 08:00 et 17:00.'
            ], Response::HTTP_BAD_REQUEST);
        }
            $existingAffectations = $affectationRepository->getAffectation([
                'technicien_id' => $technicien->getId(),
                'date_prevu' => $datePrevu
            ]);
            if (count($existingAffectations) > 0) {
                $techName = $existingAffectations[0]['technicien_nom'] . ' ' . $existingAffectations[0]['technicien_prenom'];
                return $this->json(
                    [
                        'error' => 'Le technicien est déjà affecté à cette date.',
                        'details' => [
                            'technicien' => $techName,
                            'date' => $datePrevu->format('Y-m-d H:i')
                        ]
                    ],
                    Response::HTTP_CONFLICT
                );
            }
            $qb = $autorisationSortieRepository->createQueryBuilder('a')
            ->where('a.technicien = :technicien_id')
            ->andWhere('a.statutAutorisation = :statut')
            ->andWhere(':date_prevu BETWEEN a.dateDebut AND a.dateFin')
            ->setParameter('technicien_id', $technicien->getId())
            ->setParameter('statut', 'ACCEPTER')
            ->setParameter('date_prevu', $datePrevu);

        $autorisations = $qb->getQuery()->getResult();

        if (count($autorisations) > 0) {
            $autorisation = $autorisations[0];
            return $this->json(
                [
                    'error' => 'Le technicien est en autorisation de sortie à cette date.',
                    'details' => [
                        'dateDebut' => $autorisation->getDateDebut()->format('Y-m-d H:i'),
                        'dateFin' => $autorisation->getDateFin()->format('Y-m-d H:i'),
                        'raison' => $autorisation->getRaison()
                    ]
                ],
                Response::HTTP_CONFLICT
            );
        }
                $startOfDay = (clone $datePrevu)->setTime(0, 0, 0);
        $endOfDay = (clone $datePrevu)->setTime(23, 59, 59);
        $affectationsDuJour = $affectationRepository->createQueryBuilder('a')
            ->where('a.technicien = :technicien')
            ->andWhere('a.datePrevu BETWEEN :start AND :end')
            ->setParameter('technicien', $technicien)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();

        if (count($affectationsDuJour) >= 8) {
            return $this->json([
                'error' => 'Le technicien ne peut pas avoir plus de 8 affectations par jour.'
            ], Response::HTTP_CONFLICT);
        }

        // Vérifie l'espacement d’au moins 1h entre deux affectations
        foreach ($affectationsDuJour as $affectationExistante) {
            $diffInSeconds = abs($affectationExistante->getDatePrevu()->getTimestamp() - $datePrevu->getTimestamp());
            if ($diffInSeconds < 3600) { // moins d'une heure
                return $this->json([
                    'error' => 'Il doit y avoir au moins 1 heure entre deux affectations.'
                ], Response::HTTP_CONFLICT);
            }
        }


            // 6. Vérification existence affectation existante pour la demande
            if (!$demande->getAffecterDemandes()->isEmpty()) {
                return $this->json(
                    ['error' => 'Cette demande a déjà une affectation.'],
                    Response::HTTP_CONFLICT
                );
            }
    
            // 7. Création de l'affectation
            $affectation = new AffecterDemande();
            $affectation->setDemande($demande)
                        ->setTechnicien($technicien)
                        ->setDatePrevu($datePrevu);
                        $demande->setIsAffecter(true);
                      
           
    
            // 9. Persistance
            $em->persist($affectation);
            $notif = new Notification();
           $notif->setTitre('Bonjour');
           $notif->setMessage(message: 'Nouvelle affectation pour vous.');
           $notif->setIsRead(isRead: false);
           $notif->setCreatedAt(new \DateTimeImmutable());
           $notif->setUsers($technicien);

        $em->persist($notif);

        // Envoi de l'email
        $email = (new Email())
            ->from('test@gmail.com')
            ->to($technicien->getEmail())
            ->subject(sprintf('Affectation'))
            ->html($this->renderView('emails/nouveau_Affectation.html.twig', [
               'technicien' => $technicien,
                'demande' => $demande,
                'date_prevu' => $datePrevu->format('Y-m-d H:i'),

               
            ]));

        $mailer->send($email);
        $logger->info('Email envoyé à ' . $technicien->getEmail());
    
            $em->flush();
    
            // 10. Message de succès
            return $this->json(
                ['message' => 'Affectation créée avec succès.'],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            // Message d'erreur en cas d'exception
            return $this->json(
                ['error' => 'Une erreur est survenue : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    
   

    #[Route('/getAffectation', name: 'get_affectation', methods: ['GET'])]
    public function getAffectation(
        Request $request,
        AffecterDemandeRepository $affectationRepository,
        AutorisationSortieRepository $autorisationSortieRepository

    ): JsonResponse {
        try {
            $filters = [
                'technicien_id' => $request->query->get('technicien_id'),
                'date_prevu' => $request->query->get('date_prevu') 
                    ? new \DateTime($request->query->get('date_prevu')) 
                    : null,
                    'statuts' => ['EN_ATTENTE', 'EN_COURS','TERMINEE']
                    
            ];
            
            
            $affectations = $affectationRepository->getAffectation(
                array_filter($filters) // Ne garde que les filtres non nuls
            );
            
            $qb = $autorisationSortieRepository->createQueryBuilder('a')
            ->select('a.id, a.dateDebut, a.dateFin, a.raison')
            ->where('a.statutAutorisation = :statut')
            ->setParameter('statut', 'ACCEPTER');


        if (!empty($filters['technicien_id'])) {
            $qb->andWhere('a.technicien = :technicien_id')
               ->setParameter('technicien_id', $filters['technicien_id']);
        }

        $autorisations = $qb->getQuery()->getArrayResult();
        

return $this->json([
            'affectations' => $affectations,
            'autorisations' => $autorisations
        ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Une erreur est survenue : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
#[Route('/getAffectationss', name: 'get_affectationss', methods: ['GET'])]
public function getAffectationsTechnicien(
    Request $request,
    AffecterDemandeRepository $affectationRepo,
    AutorisationSortieRepository $autorisationRepo,
    TechnicienRepository $technicienRepo
): JsonResponse {
    $email = $request->query->get('email');
    $technicien = $technicienRepo->findOneBy(['email' => $email]);

    if (!$technicien) {
        return $this->json(['message' => 'Technicien non trouvé'], 404);
    }

    $affectations = $affectationRepo->getAffectationWithDetails(['email' => $email]);
    $autorisations = $autorisationRepo->createQueryBuilder('a')
    ->where('a.technicien = :technicien')
    ->andWhere('a.statutAutorisation = :statut')
    ->setParameter('technicien', $technicien)
        ->setParameter('statut', StatutAutorisation::ACCEPTER)
    ->orderBy('a.dateDebut', 'DESC') // Trier par dateDebut en ordre décroissant
    ->getQuery()
    ->getResult();
    
    return $this->json([
        'affectations' => $affectations,

        'autorisations' => array_map(function ($auto) {
            return [
                'id' => $auto->getId(),
                'raison' => $auto->getRaison(),
                'dateDebut' => $auto->getDateDebut()->format('Y-m-d H:i:s'),
                 'dateFin' => $auto->getDateFin()->format('Y-m-d H:i:s'),

            ];
        }, $autorisations)
    ]);
}

}