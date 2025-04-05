<?php

namespace App\Controller;

use App\Repository\PremissionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
final class PremissionVontrollerController extends AbstractController{
    #[Route(path: '/getPremission', name: 'app_getPremission', methods: ['GET'])]
    public function getPremission(PremissionRepository $permissionRepository): JsonResponse
    {
        // Récupérer toutes les permissions depuis la base de données
        $permissions = $permissionRepository->findAll();

        // Formater les données pour la réponse JSON
        $permissionList = array_map(function ($permission) {
            return [
                'id' => $permission->getId(),
                'name' => $permission->getNomPremission(),
            ];
        }, $permissions);

        // Retourner les permissions sous forme de JSON
        return new JsonResponse($permissionList, 200);
    }
    
    #[Route('/updatePremission/{id}', name: 'app_update_premission', methods: ['PUT'])]
    public function updatePremission(int $id, Request $request, PremissionRepository $permissionRepository): JsonResponse
    {
        // Récupérer la permission par son ID
        $permission = $permissionRepository->find($id);

        if (!$permission) {
            return new JsonResponse(['message' => 'Permission non trouvée'], 404);
        }

        // Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les champs de la permission
        if (isset($data['nom_premission'])) {
            $permission->setNomPremission($data['nom_premission']);
        }

        // Sauvegarder les modifications dans la base de données
        $permissionRepository->save($permission);

        return new JsonResponse(['message' => 'Permission mise à jour avec succès'], 200);
    }
    }
