<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\StatutAffectation;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InterventionRepository;
use App\Repository\AffecterDemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]

final class InterventionController extends AbstractController{
    #[Route('/intervention', name: 'create_intervention', methods: ['POST'])]
public function createIntervention(
    Request $request,
    AffecterDemandeRepository $affectationRepository,
    TacheRepository $tacheRepository,
    EntityManagerInterface $em
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $affectationId = $data['affectation_id'] ?? null;
    $observation = $data['observation'] ?? null;
    $tacheIds = $data['taches'] ?? []; // tableau d'IDs cochés

    if (!$affectationId || !$observation || empty($tacheIds)) {
        return new JsonResponse(['status' => 'error', 'message' => 'Champs manquants.'], 400);
    }
    $affectation = $affectationRepository->find($affectationId);
    if (!$affectation) {
        return $this->json(['error' => 'Affectation introuvable'], 404);
    }

    $intervention = new Intervention();
    $intervention->setAffectation($affectation);
    $intervention->setObservation($observation);
    $intervention->setDateFin(new \DateTime());
    $intervention->setAffectation($affectation);

    foreach ($tacheIds as $tacheId) {
        $tache = $tacheRepository->find($tacheId);
        if ($tache) {
            $intervention->addTach($tache);
        }
    }
    $affectation->setStatutAffectation(StatutAffectation::TERMINEE);
    $em->persist($intervention);
    $em->flush();

    return $this->json(['message' => 'Intervention créée et affectation terminée.']);
}

#[Route('/getAllInterventions', name: 'get_all_interventions', methods: ['GET'])]
public function getAllInterventions(InterventionRepository $interventionRepository): JsonResponse
{
    // Récupérer toutes les interventions via le repository
    $interventions = $interventionRepository->findAllInterventions();

    // Formater les données pour la réponse JSON
    $response = [];
    foreach ($interventions as $intervention) {
        // Récupérer les tâches associées à l'intervention
        $interventionEntity = $interventionRepository->find($intervention['intervention_id']);
        $taches = [];
        foreach ($interventionEntity->getTaches() as $tache) {
            $taches[] = [
                'tache' => $tache->getTache(),
                'prix' => $tache->getPrixTache(),
            ];
        }

        $response[] = [
            'intervention_id' => $intervention['intervention_id'],
            'date_fin' => $intervention['intervention_date_fin'] ? $intervention['intervention_date_fin']->format('Y-m-d H:i:s') : null,
            'observation' => $intervention['intervention_observation'],
            'affectation_date_prevu' => $intervention['affectation_date_prevu'] ? $intervention['affectation_date_prevu']->format('Y-m-d H:i:s') : null,
            'demande' => [
                'id' => $intervention['demande_id'],
                'description' => $intervention['demande_description'],
            ],
            'client' => [
                'entreprise' => $intervention['client_entreprise'],
                'nom' => $intervention['client_nom'],
                'prenom' => $intervention['client_prenom'],
            ],
            'technicien' => [
                'nom' => $intervention['technicien_nom'],
                'prenom' => $intervention['technicien_prenom'],
            ],
            'taches' => $taches,
        ];
    }

    return $this->json([
        'status' => 'success',
        'data' => $response
    ]);
}

#[Route('/getClientInterventions', name: 'get_client_interventions', methods: ['GET'])]
public function getClientInterventions(
    Request $request,
    InterventionRepository $interventionRepository
): JsonResponse {
    // Récupérer l'email du client connecté depuis la requête
    $email = $request->query->get('email');

    if (!$email) {
        return $this->json(['status' => 'error', 'message' => 'Email requis.'], Response::HTTP_BAD_REQUEST);
    }

    // Récupérer les interventions pour le client
    $interventions = $interventionRepository->findInterventionsByClientEmail($email);

    // Formater les données pour la réponse JSON
    $response = [];
    foreach ($interventions as $intervention) {
        $interventionEntity = $interventionRepository->find($intervention['intervention_id']);
        $taches = [];
        foreach ($interventionEntity->getTaches() as $tache) {
            $taches[] = [
                'tache' => $tache->getTache(),
                'prix' => $tache->getPrixTache(),
            ];
        }
        $response[] = [
            'intervention_id' => $intervention['intervention_id'],
            'date_fin' => $intervention['intervention_date_fin'] ? $intervention['intervention_date_fin']->format('Y-m-d H:i:s') : null,
            'observation' => $intervention['intervention_observation'],
            'affectation_date_prevu' => $intervention['affectation_date_prevu'] ? $intervention['affectation_date_prevu']->format('Y-m-d H:i:s') : null,
            'demande' => [
                'id' => $intervention['demande_id'],
                'description' => $intervention['demande_description'],
            ],
            'client' => [
                'entreprise' => $intervention['client_entreprise'],
                'nom' => $intervention['client_nom'],
                'prenom' => $intervention['client_prenom'],
            ],
            'technicien' => [
                'nom' => $intervention['technicien_nom'],
                'prenom' => $intervention['technicien_prenom'],
            ],
            'taches' => $taches,

        ];
    }

    return $this->json([
        'status' => 'success',
        'data' => $response
    ]);
}
// #[Route('/interventions', name: 'get_interventions', methods: ['GET'])]
// public function getInterventions(
//     Request $request,
//     InterventionRepository $interventionRepository
// ): JsonResponse {
//     $user = $this->getUser();

//     if (!$user) {
//         return $this->json([
//             'status' => 'error',
//             'message' => 'Unauthorized'
//         ], JsonResponse::HTTP_UNAUTHORIZED);
//     }

//     // ADMIN: récupère tout
//     if ($this->isGranted('ROLE_ADMIN')) {
//         $interventions = $interventionRepository->findAllInterventions();
//     }
//     // CLIENT: récupère seulement ses interventions
//     else if ($this->isGranted('ROLE_CLIENT')) {
//         $email = $request->query->get('email');
//         if (!$email) {
//             return $this->json([
//                 'status' => 'error',
//                 'message' => 'Email manquant pour le client'
//             ], JsonResponse::HTTP_BAD_REQUEST);
//         }
//         $interventions = $interventionRepository->findInterventionsByClientEmail($email);
//     } else {
//         return $this->json([
//             'status' => 'error',
//             'message' => 'Access denied'
//         ], JsonResponse::HTTP_FORBIDDEN);
//     }

//     $formatted = $this->formatInterventionResponse($interventions, $interventionRepository);

//     return $this->json([
//         'status' => 'success',
//         'data' => $formatted
//     ]);
// }

// private function formatInterventionResponse(array $interventions, InterventionRepository $repo): array
// {
//     $response = [];

//     foreach ($interventions as $intervention) {
//         $entity = $repo->find($intervention['intervention_id']);
//         $taches = [];

//         foreach ($entity->getTaches() as $tache) {
//             $taches[] = [
//                 'tache' => $tache->getTache(),
//                 'prix' => $tache->getPrixTache(),
//             ];
//         }

//         $response[] = [
//             'intervention_id' => $intervention['intervention_id'],
//             'date_fin' => $intervention['intervention_date_fin']?->format('Y-m-d H:i:s'),
//             'observation' => $intervention['intervention_observation'],
//             'affectation_date_prevu' => $intervention['affectation_date_prevu']?->format('Y-m-d H:i:s'),
//             'demande' => [
//                 'id' => $intervention['demande_id'],
//                 'description' => $intervention['demande_description'],
//             ],
//             'client' => [
//                 'nom' => $intervention['client_nom'],
//                 'prenom' => $intervention['client_prenom'],
//             ],
//             'technicien' => [
//                 'nom' => $intervention['technicien_nom'],
//                 'prenom' => $intervention['technicien_prenom'],
//             ],
//             'taches' => $taches,
//         ];
//     }

//     return $response;
// }

}
