<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\statutFacture;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]

final class ModePaiementController extends AbstractController{
    #[Route('/getmodePaiement', name: 'get_tmodePaiement', methods: ['GET'])]
    public function getTaches(ModePaiementRepository $modePaiementRepository): JsonResponse
    {
        $modePaiement = $modePaiementRepository->findAllMode();

        return $this->json([
            'status' => 'success',
            'data' => $modePaiement
        ]);
    }
    // src/Controller/FactureController.php
#[Route('/modes-paiement/{id}', name: 'api_modes_paiement', methods: ['POST'])]
public function setModesPaiement(
    Facture $facture,
    Request $request,
    ModePaiementRepository $modeRepo,
    EntityManagerInterface $em
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['modes']) || !is_array($data['modes'])) {
        return $this->json(['error' => 'Liste de modes manquante'], 400);
    }

    foreach ($data['modes'] as $modeId) {
        $mode = $modeRepo->find($modeId);
        if ($mode) {
            $facture->addModePaiement($mode);
        }
    }

   
    $now = new \DateTimeImmutable();

    if ($facture->getDateEcheance() < $now) {
        // Facture en retard
        $facture->setStatut(StatutFacture::RETARD);
    } else {
        // Facture payée
        $facture->setStatut(StatutFacture::PAYEE);
    }

    $em->persist($facture);
    $em->flush();

    return $this->json([
        'success' => true,
        'statut' => $facture->getStatut()->value,  // Renvoyer la valeur string de l'enum
    ]);

}
}