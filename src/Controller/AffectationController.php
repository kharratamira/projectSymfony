<?php

namespace App\Controller;

use App\Entity\AffecterDemande;
use App\Repository\TechnicienRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AffecterDemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
    
            if (!$demande || !$technicien) {
                return $this->json(
                    ['error' => 'Demande ou technicien introuvable.'],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            // 4. Vérification de l'état de la demande
            
            if ($demande->getStatut()->value !== 'accepter') {
                return $this->json(
                    ['error' => 'La demande doit être dans un état accepté pour être affectée.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $datePrevu = new \DateTime($data['date_prevu']);
            $currentDate = new \DateTime();
            if ($datePrevu < $currentDate) {
                return $this->json(
                    ['error' => 'La date prévue doit être égale ou postérieure à la date actuelle.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            // 5. Vérification de la disponibilité du technicien
            // $existingAffectation = $affectationRepository->findOneBy([
            //     'technicien' => $technicien,
            //     'datePrevu' => $datePrevu
            // ]);
    
            // if ($existingAffectation) {
            //     return $this->json(
            //         ['error' => 'Le technicien est déjà affecté à une autre demande pour cette date.'],
            //         Response::HTTP_CONFLICT
            //     );
            // }
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
            // 8. Validation
            $errors = $validator->validate($affectation);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(
                    ['errors' => $errorMessages],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
    
            // 9. Persistance
            $em->persist($affectation);
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
    
    // #[Route('/getAffectationss', name: 'get_affectationss', methods: ['GET'])]
    // public function getAffectations(
    //     Request $request,
    //     AffecterDemandeRepository $affectationRepository
    // ): JsonResponse {
    //     try {
    //         $affectations = $affectationRepository->getAffectation();
    
    //         return $this->json($affectations, Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return $this->json(
    //             ['error' => 'Une erreur est survenue : ' . $e->getMessage()],
    //             Response::HTTP_INTERNAL_SERVER_ERROR
    //         );
    //     }
    // }

    #[Route('/getAffectation', name: 'get_affectation', methods: ['GET'])]
    public function getAffectation(
        Request $request,
        AffecterDemandeRepository $affectationRepository
    ): JsonResponse {
        try {
            $filters = [
                'technicien_id' => $request->query->get('technicien_id'),
                'date_prevu' => $request->query->get('date_prevu') 
                    ? new \DateTime($request->query->get('date_prevu')) 
                    : null
                    
            ];
            
            
            $affectations = $affectationRepository->getAffectation(
                array_filter($filters) // Ne garde que les filtres non nuls
            );
    
            return $this->json($affectations, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Une erreur est survenue : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
////pour chaque technicien voir la calandier specifique 
#[Route('/getAffectationss', name: 'get_affectationss', methods: ['GET'])]
    public function getAffectationss(
        Request $request,
        AffecterDemandeRepository $affectationRepository
    ): JsonResponse {
        try {
            $email = $request->query->get('email');
            
            if (!$email) {
                return $this->json(
                    ['error' => 'Email du technicien requis'],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $affectations = $affectationRepository->findByTechnicienEmail($email);
            foreach ($affectations as &$aff) {
                if ($aff['datePrevu'] instanceof \DateTime) {
                    $aff['datePrevu'] = $aff['datePrevu']->format('Y-m-d\TH:i:sP');
                }}
            return $this->json($affectations, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Une erreur est survenue : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

}