<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\StatutAffectation;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
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

}
