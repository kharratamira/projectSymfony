<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\statutFacture;
use Symfony\Component\Mime\Email;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InterventionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]

final class FactureController extends AbstractController{

// #[Route('/genererfacture/{id}', name: 'generer_facture', methods: ['POST'])]
// public function genererFacture(
//     int $id,
//     Request $request,
//     EntityManagerInterface $em,
//     InterventionRepository $interventionRepository
// ): JsonResponse {
//     $intervention = $interventionRepository->find($id);

//     if (!$intervention) {
//         return $this->json(['error' => 'Intervention introuvable.'], 404);
//     }

//     $taches = $intervention->getTaches();
//     if (count($taches) === 0) {
//         return $this->json(['error' => 'Aucune tâche liée à cette intervention.'], 400);
//     }

//     $montantHTVA = array_reduce($taches->toArray(), fn($total, $t) => $total + $t->getPrixTache(), 0);
//     $TVA = round($montantHTVA * 0.2, 2);
//     $montantTTC = round($montantHTVA + $TVA, 2);

//     $data = json_decode($request->getContent(), true);
//     $remise = isset($data['remise']) ? floatval($data['remise']) : 0;
//         $montantAvecRemise = $montantTTC;

//     if ($remise > 0 && $remise <= 100) {
//         $montantAvecRemise = round($montantTTC - ($montantTTC * ($remise / 100)), 2);
//     }

//     $facture = new Facture();
//     $facture->setNumFacture('FAC-' . strtoupper(uniqid()))
//         ->setDateEmission(new \DateTime())
//         ->setDateEcheance((new \DateTime())->modify('+30 days'))
//         ->setMontantHTVA($montantHTVA)
//         ->setTVA($TVA)
//         ->setMontantTTC($montantAvecRemise)
//         ->setRemise($remise)
//         ->setStatut(statutFacture::EN_ATTENTE)
//         ->setIntervention($intervention);

//     $em->persist($facture);
//     $em->flush();

//     return $this->json([
//         'numFacture' => $facture->getNumFacture(),
//         'dateEmission' => $facture->getDateEmission()?->format('Y-m-d'),
//         'dateEcheance' => $facture->getDateEcheance()?->format('Y-m-d'),
//         'montantHTVA' => $facture->getMontantHTVA(),
//         'tva' => $facture->getTVA(),
//         'montantTTC' => $facture->getMontantTTC(),
//         'remise' => $facture->getRemise(),
//         'statut' => $facture->getStatut()->value,
//         'intervention' => [
//             'observation' => $facture->getIntervention()?->getObservation(),
//             'taches' => array_map(fn($t) => [
//                 'tache' => $t->getTache(),
//                 'prixTache' => $t->getPrixTache(),
//             ], $facture->getIntervention()?->getTaches()->toArray() ?? [])
//         ]
//     ]);

// }
#[Route('/genererfacture/{id}', name: 'generer_facture', methods: ['POST'])]
public function genererFacture(
    int $id,
    Request $request,
    EntityManagerInterface $em,
    InterventionRepository $interventionRepository,
    MailerInterface $mailer,
    LoggerInterface $logger
): JsonResponse {
    try {
        $intervention = $interventionRepository->find($id);

        if (!$intervention) {
            return $this->json(['error' => 'Intervention introuvable.'], 404);
        }

        $taches = $intervention->getTaches();
        if (count($taches) === 0) {
            return $this->json(['error' => 'Aucune tâche liée à cette intervention.'], 400);
        }

        // Calcul des montants
        $montantHTVA = array_reduce($taches->toArray(), fn($total, $t) => $total + $t->getPrixTache(), 0);
        $TVA = round($montantHTVA * 0.2, 2);
        $montantTTC = round($montantHTVA + $TVA, 2);

        $data = json_decode($request->getContent(), true);
        $remise = isset($data['remise']) ? floatval($data['remise']) : 0;
        $montantAvecRemise = $montantTTC;

        if ($remise > 0 && $remise <= 100) {
            $montantAvecRemise = round($montantTTC - ($montantTTC * ($remise / 100)), 2);
        }

        // Création de la facture
        $facture = new Facture();
        $facture->setNumFacture('FAC-' . strtoupper(uniqid()))
            ->setDateEmission(new \DateTime())
            ->setDateEcheance((new \DateTime())->modify('+30 days'))
            ->setMontantHTVA($montantHTVA)
            ->setTVA($TVA)
            ->setMontantTTC($montantAvecRemise)
            ->setRemise($remise)
            ->setStatut(StatutFacture::EN_ATTENTE)
            ->setIntervention($intervention);

        $em->persist($facture);

        // CORRECTION: Accès au client via la relation correcte
        $affectation = $intervention->getAffectation();
        if (!$affectation) {
            throw new \Exception("Aucune affectation trouvée pour cette intervention");
        }

        $demande = $affectation->getDemande(); // Nom correct de la méthode
        if (!$demande) {
            throw new \Exception("Aucune demande trouvée pour cette affectation");
        }

        $client = $demande->getClient();
        if (!$client) {
            throw new \Exception("Aucun client trouvé pour cette demande");
        }

        // Création de la notification
        $notif = new Notification();
        $notif->setTitre('Votre facture a été générée')
              ->setMessage(sprintf('Votre facture %s a été créée avec succès. Montant TTC: %s Dt', 
                  $facture->getNumFacture(), 
                  number_format($montantAvecRemise, 2, ',', ' ')))
              ->setIsRead(false)
              ->setCreatedAt(new \DateTimeImmutable())
              ->setUsers($client); // Vérifiez setUser() vs setUsers()

        $em->persist($notif);

        // Envoi de l'email
        $email = (new Email())
            ->from('test@gmail.com')
            ->to($client->getEmail())
            ->subject(sprintf('Facture %s générée', $facture->getNumFacture()))
            ->html($this->renderView('emails/nouveau_Facteur.html.twig', [
                'client' => $client,
                'facture' => $facture,
                'montant' => number_format($montantAvecRemise, 2, ',', ' ')
            ]));

        $mailer->send($email);
        
        $em->flush();

        return $this->json([
        'numFacture' => $facture->getNumFacture(),
        'dateEmission' => $facture->getDateEmission()?->format('Y-m-d'),
        'dateEcheance' => $facture->getDateEcheance()?->format('Y-m-d'),
        'montantHTVA' => $facture->getMontantHTVA(),
        'tva' => $facture->getTVA(),
        'montantTTC' => $facture->getMontantTTC(),
        'remise' => $facture->getRemise(),
        'statut' => $facture->getStatut()->value,
        'intervention' => [
            'observation' => $facture->getIntervention()?->getObservation(),
            'taches' => array_map(fn($t) => [
                'tache' => $t->getTache(),
                'prixTache' => $t->getPrixTache(),
            ], $facture->getIntervention()?->getTaches()->toArray() ?? [])
        ]
    ]);

    } catch (\Exception $e) {
        $logger->error('Erreur génération facture', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return $this->json([
            'error' => 'Erreur lors de la génération de la facture',
            'details' => $e->getMessage()
        ], 500);
    }
}
#[Route('/facturepreview/{id}', name: 'facture_preview', methods: ['GET'])]
public function previewFacture(
    int $id,
    InterventionRepository $interventionRepository
): JsonResponse {
    $intervention = $interventionRepository->find($id);

    if (!$intervention) {
        return $this->json(['error' => 'Intervention introuvable.'], 404);
    }

    $taches = $intervention->getTaches();
    if (count($taches) === 0) {
        return $this->json(['error' => 'Aucune tâche liée à cette intervention.'], 400);
    }

    $montantHTVA = array_reduce($taches->toArray(), fn($total, $t) => $total + $t->getPrixTache(), 0);
    $TVA = round($montantHTVA * 0.2, 2); // TVA 20%
    $montantTTC = round($montantHTVA + $TVA, 2);

    $client = $intervention->getAffectation()?->getDemande()?->getClient();

    return $this->json([
        'numFacture' => 'PREVIEW-' . strtoupper(uniqid()),
        'dateEmission' => (new \DateTime())->format('Y-m-d'),
        'dateEcheance' => (new \DateTime())->modify('+30 days')->format('Y-m-d'),
        'montantHTVA' => $montantHTVA,
        'TVA' => $TVA,
        'montantTTC' => $montantTTC,
        'remise' => 0,
        'statut' => statutFacture::EN_ATTENTE->value,
        'client' => $client ? [
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'entreprise' => $client->getEntreprise(),
            'email' => $client->getEmail()
        ] : null,
        'intervention' => [
            'observation' => $intervention->getObservation(),
            'taches' => array_map(fn($t) => [
                'tache' => $t->getTache(),
                'prixTache' => $t->getPrixTache(),
            ], $taches->toArray())
        ]
    ]);
}
 #[Route('/factures', name: 'get_all_factures', methods: ['GET'])]
    public function getAllFactures(FactureRepository $factureRepository): JsonResponse
    {
        $factures = $factureRepository->findAll();

        $response = [];
        foreach ($factures as $facture) {
            $intervention = $facture->getIntervention();
            $client = $intervention?->getAffectation()?->getDemande()?->getClient();
  $modesPaiement = [];
        foreach ($facture->getModePaiements() as $mode) {
            $modesPaiement[] = [
                'id' => $mode->getId(),
                'nom' => $mode->getModePaiement(),
            ];
        }
            $response[] = [
                'id' => $facture->getId(),
                'numFacture' => $facture->getNumFacture(),
                'dateEmission' => $facture->getDateEmission()?->format('Y-m-d'),
                'dateEcheance' => $facture->getDateEcheance()?->format('Y-m-d'),
                'montantHTVA' => $facture->getMontantHTVA(),
                'tva' => $facture->getTVA(),
                'montantTTC' => $facture->getMontantTTC(),
                'remise' => $facture->getRemise(),
                'statut' => $facture->getStatut()->value,
                'modePaiements' => $modesPaiement, // ✅ ajouté ici


                'intervention' => $intervention ? [
                    'id' => $intervention->getId(),
                    'observation' => $intervention->getObservation(),
                    'dateFin' => $intervention->getDateFin()?->format('Y-m-d'),
                    'taches' => array_map(fn($t) => [
                        'tache' => $t->getTache(),
                        'prixTache' => $t->getPrixTache(),
                    ], $intervention->getTaches()->toArray())
                ] : null,
                'client' => $client ? [
                    'nom' => $client->getNom(),
                    'prenom' => $client->getPrenom(),
                    'email' => $client->getEmail(),
                    'entreprise' => $client->getEntreprise(),
                    
                ] : null,
            ];
        }

        return $this->json(['status' => 'success', 'data' => $response]);
    }

    // ✅ GET FACTURES PAR CLIENT (par email)
    #[Route('/facturesclient', name: 'get_factures_by_client', methods: ['GET'])]
public function getFacturesByClient(Request $request, FactureRepository $factureRepository): JsonResponse
{
    $email = $request->query->get('email');

    if (!$email) {
        return $this->json(['error' => 'Email client requis.'], 400);
    }

    $factures = $factureRepository->findFacturesByClientEmail($email);

    $response = [];

    foreach ($factures as $facture) {
        // Récupérer les modes de paiement pour chaque facture
        $modesPaiement = [];
        foreach ($facture->getModePaiements() as $mode) {
            $modesPaiement[] = [
                'id' => $mode->getId(),
                'nom' => $mode->getModePaiement(),
            ];
        }

        $response[] = [
            'id' => $facture->getId(),
            'numFacture' => $facture->getNumFacture(),
            'dateEmission' => $facture->getDateEmission()?->format('Y-m-d'),
            'dateEcheance' => $facture->getDateEcheance()?->format('Y-m-d'),
            'montantHTVA' => $facture->getMontantHTVA(),
            'tva' => $facture->getTVA(),
            'montantTTC' => $facture->getMontantTTC(),
            'remise' => $facture->getRemise(),
            'statut' => $facture->getStatut()->value,
            'modePaiements' => $modesPaiement,
            'intervention' => [
                'id' => $facture->getIntervention()->getId(),
                'observation' => $facture->getIntervention()->getObservation(),
                'dateFin' => $facture->getIntervention()->getDateFin()?->format('Y-m-d'),
                'taches' => array_map(fn($t) => [
                    'tache' => $t->getTache(),
                    'prixTache' => $t->getPrixTache(),
                ], $facture->getIntervention()->getTaches()->toArray())
            ],
            'client' => [
                'nom' => $facture->getIntervention()->getAffectation()->getDemande()->getClient()->getNom(),
                'prenom' => $facture->getIntervention()->getAffectation()->getDemande()->getClient()->getPrenom(),
                'email' => $facture->getIntervention()->getAffectation()->getDemande()->getClient()->getEmail(),
                'entreprise' => $facture->getIntervention()->getAffectation()->getDemande()->getClient()->getEntreprise(),
            ]
        ];
    }

    return $this->json(['status' => 'success', 'data' => $response]);
}

}
