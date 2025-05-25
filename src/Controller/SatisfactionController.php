<?php

namespace App\Controller;

use App\Entity\SatisfactionClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InterventionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SatisfactionClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class SatisfactionController extends AbstractController{
  #[Route('/satisfaction', name: 'api_satisfaction', methods: ['POST'])]
public function createSatisfaction(
    Request $request,
    EntityManagerInterface $em,
    InterventionRepository $interventionRepo
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $niveau = $data['niveau'] ?? null;
    $commentaire = $data['commentaire'] ?? null;
    $interventionId = $data['intervention_id'] ?? null;

    if (!$niveau || !$interventionId) {
        return new JsonResponse(['error' => 'Données manquantes.'], 400);
    }

    $intervention = $interventionRepo->find($interventionId);
    if (!$intervention) {
        return new JsonResponse(['error' => 'Intervention introuvable.'], 404);
    }

    $satisfaction = new SatisfactionClient();
    $satisfaction->setNiveau($niveau);
    $satisfaction->setCommentaire($commentaire);
    $satisfaction->setIntervention($intervention);
    $satisfaction->setDateCreation(new \DateTimeImmutable());

    $em->persist($satisfaction);
    $em->flush();

    return new JsonResponse(['message' => 'Satisfaction enregistrée avec succès.']);
}
#[Route('/getSatisfaction', name: 'get_all_satisfaction', methods: ['GET'])]
public function getAllSatisfaction(SatisfactionClientRepository $satisfactionRepository,InterventionRepository $intervention): JsonResponse
{
    $satisfactions = $satisfactionRepository->findAllsatisfactionClient();

 $response = array_map(function($item) use ($intervention) {
        // Récupérer l'entité Intervention complète avec ses relations
        $intervention = $intervention->find($item['intervention_id']);
        
        // Formater les tâches de l'intervention
        $taches = [];
        if ($intervention && $intervention->getTaches()) {
            foreach ($intervention->getTaches() as $tache) {
                $taches[] = [
                    'id' => $tache->getId(),
                    'tache' => $tache->getTache(),
                    'prix' => $tache->getPrixTache(),
                ];
            }
        }        return [
            'satisfaction' => [
                'id' => $item['satisfaction_id'],
                'niveau' => $item['satisfaction_niveau'],
                'commentaire' => $item['satisfaction_commentaire'],
                'date_creation' => $item['satisfaction_date_creation']->format('Y-m-d H:i:s'),
            ],
            'intervention' => [
                'id' => $item['intervention_id'],
                'date_fin' => $item['intervention_date_fin']?->format('Y-m-d H:i:s'),
                'observation' => $item['intervention_observation'],
                  'taches' => $taches ,// Ajout des tâches
            
            
            'affectation' => [
                'id' => $item['affectation_id'],
                'date_prevu'=>$item['affectation_date_prevu']->format('Y-m-d H:i:s'),
            ],
           
            'demande' => [
                'id' => $item['demande_id'],
                'description' => $item['demande_description'],
            ],
            'client' => [
                'id' => $item['client_id'],
                'entreprise' => $item['client_entreprise'],
                'nom' => $item['client_nom'],
                'prenom' => $item['client_prenom'],
                'email' => $item['client_email'],
            ],
            'technicien' => [
                'id' => $item['technicien_id'],
                'nom' => $item['technicien_nom'],
                'prenom' => $item['technicien_prenom'],
                'specialite'=>$item['technicien_specialite']
            ]
         ] ];
    }, $satisfactions);

    return $this->json([
        'status' => 'success',
        'data' => $response
    ]);
}}