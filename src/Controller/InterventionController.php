<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Notification;
use App\Entity\StatutAffectation;
use Symfony\Component\Mime\Email;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InterventionRepository;
use App\Repository\AffecterDemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
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
    EntityManagerInterface $em,
    MailerInterface $mailer
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
    $demande = $affectation->getDemande();
    $client = $demande->getClient(); // méthode à adapter selon ta relation

    // 🔔 Création de la notification
    $notification = new Notification();
    $notification->setTitre('Intervention terminée')
        ->setMessage('L\'intervention liée à votre contrat a été terminée avec succès. Merci de remplir le formulaire de satisfaction.')
        ->setIsRead(false)
        ->setCreatedAt(new \DateTimeImmutable())
        ->setUsers($client); // ou ->setUser($client) selon ta classe

    $em->persist($notification);

    // 📧 Envoi de l'email avec template Twig
    $email = (new Email())
        ->from('support@tonapp.com')
        ->to($client->getEmail())
        ->subject('Intervention terminée - Merci de remplir le formulaire')
        ->html($this->renderView('emails/intervention_terminee.html.twig', [
            'client' => $client,
            'intervention' => $intervention,
            'affectation' => $affectation,
        ]));

    $mailer->send($email);

    $em->flush();
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
 $factureEntity = $interventionEntity->getFacture(); // Assure-toi que Intervention a getFacture()

        $facture = null;
        if ($factureEntity) {
            $facture = [
                'numFacture' => $factureEntity->getNumFacture(),
                'dateEmission' => $factureEntity->getDateEmission()?->format('Y-m-d H:i:s'),
                'dateEcheance' => $factureEntity->getDateEcheance()?->format('Y-m-d'),
                'montantHTVA' => $factureEntity->getMontantHTVA(),
                'TVA' => $factureEntity->getTVA(),
                'montantTTC' => $factureEntity->getMontantTTC(),
                'remise' => $factureEntity->getRemise(),
                'statut' => $factureEntity->getStatut()->value,
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
            'facture' => $facture,
            'taches' => $taches,
        ];
    }

    return $this->json([
        'status' => 'success',
        'data' => $response
    ]);
}

#[Route('/getInterventionsByEmail', name: 'get_interventions_by_email', methods: ['GET'])]
public function getInterventionsByEmail(
    Request $request,
    InterventionRepository $interventionRepository
): JsonResponse {
    $email = $request->query->get('email');
    $role = $request->query->get('role'); // ROLE_CLIENT ou ROLE_TECHNICIEN

    if (!$email || !$role) {
        return $this->json(['status' => 'error', 'message' => 'Email et rôle requis.'], Response::HTTP_BAD_REQUEST);
    }

    try {
        $interventions = $interventionRepository->findInterventionsByEmail($email, $role);
    } catch (\InvalidArgumentException $e) {
        return $this->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
    }

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

#[Route('/updateIntervention/{id}', name: 'update_intervention', methods: ['PUT'])]
public function updateIntervention(
    int $id,
    Request $request,
    InterventionRepository $interventionRepository,
    TacheRepository $tacheRepository,
    EntityManagerInterface $entityManager
): JsonResponse {
    // Récupérer l'intervention par son ID
    $intervention = $interventionRepository->find($id);

    if (!$intervention) {
        return $this->json(['status' => 'error', 'message' => 'Intervention non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Récupérer les données de la requête
    $data = json_decode($request->getContent(), true);

    

        
    // Mettre à jour l'observation
    if (isset($data['observation'])) {
        $intervention->setObservation($data['observation']);
    }

    // Mettre à jour les tâches
    if (isset($data['taches']) && is_array($data['taches'])) {
        // Supprimer les tâches existantes
        foreach ($intervention->getTaches() as $tache) {
            $intervention->removeTach($tache);
        }

        // Ajouter les nouvelles tâches
        foreach ($data['taches'] as $tacheId) {
            $tache = $tacheRepository->find($tacheId);
            if ($tache) {
                $intervention->addTach($tache);
            }
        }
    }

    // Sauvegarder les modifications
    $entityManager->flush();

    return $this->json(['status' => 'success', 'message' => 'Intervention mise à jour avec succès.']);
}}