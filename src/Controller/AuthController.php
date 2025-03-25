<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Commercial;
use App\Entity\Technicien;
use App\Repository\RoleRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api')]
final class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/signup_client', name: 'api_signup_client', methods: ['POST'])]
    
    public function signup(Request $request, ClientRepository $clientRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier les champs requis
        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['adresse'], $data['numTel'], $data['entreprise'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        $existingClient = $clientRepository->findOneBy(['email' => $data['email']]);
        if ($existingClient) {
            return $this->json(['error' => 'Email already exists'], Response::HTTP_BAD_REQUEST);
        }

        // Création d'un nouveau client
        $client = new Client();
        $client->setEmail($data['email']);
        $client->setPassword($this->passwordEncoder->hashPassword($client, $data['password']));
        $client->setNom($data['nom']);
        $client->setPrenom($data['prenom']);
        $client->setAdresse($data['adresse']);
        $client->setNumTel($data['numTel']??null);
        $client->setEntreprise($data['entreprise']);
        $client->setDateCreation(new \DateTime());

        
        // Vérifier si le rôle 'ROLE_CLIENT' existe
        $roleClient = $this->entityManager->getRepository(Role::class)->findOneBy(['nom_role' => 'ROLE_CLIENT']);
        if (!$roleClient) {
            return $this->json(['error' => 'Role ROLE_CLIENT not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        // Ajouter le rôle au client
        $client->setRole($roleClient);

        // Validation des données
        $errors = $validator->validate($client);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrer le client en base de données
        try {
            $this->entityManager->persist($client);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while saving the client', 'details' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'Client registered successfully'], Response::HTTP_CREATED);
    }
    #[Route('/signup', name: 'api_signup', methods: ['POST'])]
   // #[IsGranted('ROLE_ADMIN')]
    public function sigupUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        RoleRepository $roleRepo
    ): JsonResponse {
        // if (!$this->isGranted('ROLE_ADMIN')) {
        //     return new JsonResponse(['message' => 'Access denied'], 403);
        // }
        $data = json_decode($request->getContent(), true);

        // On récupère le type d'utilisateur
        $userType = $data['user_type'] ?? null;

        if (!$userType) {
            return new JsonResponse(['message' => 'User type is required'], 400);
        }

        // Instanciation selon le type demandé
        switch (strtolower($userType)) {
            case 'technicien':
                $user = new Technicien();
                $user->setDisponibilite($data['disponibilite'] ?? true);
                $user->setSpecialite($data['specialite'] ?? 'Généraliste');
                break;

            case 'commercial':
                $user = new Commercial();
                $user->setRegion($data['region'] ?? 'Tunis');
                break;

                case 'admin':
                    $user = new Admin();
                    break;

            default:
                return new JsonResponse(['message' => 'Invalid user type'], 400);
        }

        // Champs communs
        $user->setNom($data['nom'] ?? '');
        $user->setPrenom($data['prenom'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setNumTel($data['numTel'] ?? '');
        
        // Gestion du mot de passe hashé
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Gestion du rôle depuis la BDD
        $roleName = strtoupper($userType); // ADMIN / TECHNICIEN / COMMERCIAL
        $role = $roleRepo->findOneBy(['nom_role' => 'ROLE_' . $roleName]);

        if (!$role) {
            return new JsonResponse(['message' => 'Role not found in DB'], 400);
        }

        $user->setRole($role);

        // On sauvegarde !
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur créé avec succès'], 201);
    }
}

