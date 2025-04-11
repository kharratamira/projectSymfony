<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Commercial;
use App\Entity\Technicien;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
public function signup(Request $request, ClientRepository $clientRepository, ValidatorInterface $validator, SluggerInterface $slugger): JsonResponse
{
    // Récupérer les données du formulaire
    $data = $request->request->all();
    $photoFile = $request->files->get('photo');

    // Vérifier les champs requis
    $requiredFields = ['email', 'password', 'nom', 'prenom', 'adresse', 'numTel', 'entreprise'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        return $this->json([
            'error' => 'Champs requis manquants',
            'missing_fields' => $missingFields
        ], Response::HTTP_BAD_REQUEST);
    }

    // Vérifier si l'email existe déjà
    $existingClient = $clientRepository->findOneBy(['email' => $data['email']]);
    if ($existingClient) {
        return $this->json([
            'error' => 'Cet email est déjà utilisé'
        ], Response::HTTP_BAD_REQUEST);
    }

    // Création d'un nouveau client
    $client = new Client();
    $client->setEmail($data['email']);
    $client->setPassword($this->passwordEncoder->hashPassword($client, $data['password']));
    $client->setNom($data['nom']);
    $client->setPrenom($data['prenom']);
    $client->setAdresse($data['adresse']);
    $client->setNumTel($data['numTel']);
    $client->setEntreprise($data['entreprise']);
    $client->setDateCreation(new \DateTime());
    $client->setIsActive(true); // Définit isActive à true par défaut

    // Gestion du rôle CLIENT
    $roleClient = $this->entityManager->getRepository(Role::class)->findOneBy(['nom_role' => 'ROLE_CLIENT']);
    if (!$roleClient) {
        return $this->json([
            'error' => 'Erreur de configuration du rôle'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    $client->setRole($roleClient);

    // Traitement de la photo si elle est fournie
    if ($photoFile) {
        // Validation du type de fichier
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $mimeType = $photoFile->getMimeType();
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return $this->json([
                'error' => 'Type de fichier non supporté (seuls JPEG, PNG et GIF sont acceptés)'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation de la taille (max 2MB)
        if ($photoFile->getSize() > 2 * 1024 * 1024) {
            return $this->json([
                'error' => 'La taille de l\'image ne doit pas dépasser 2MB'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Génération d'un nom de fichier unique
        $originalname = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $Filename = $slugger->slug($originalname);
        $newName = $Filename.'-'.uniqid().'.'.$photoFile->guessExtension();

        // Déplacement du fichier
        try {
            $photoFile->move(
                $this->getParameter('user_photos_directory'),
                $newName
            );
            $client->setPhoto($newName);
        } catch (FileException $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'enregistrement de l\'image'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Validation des données
    $errors = $validator->validate($client);
    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }
        
        return $this->json([
            'error' => 'Validation failed',
            'errors' => $errorMessages
        ], Response::HTTP_BAD_REQUEST);
    }

    // Enregistrement en base de données
    try {
        $this->entityManager->persist($client);
        $this->entityManager->flush();
    } catch (\Exception $e) {
        return $this->json([
            'error' => 'Erreur lors de l\'enregistrement',
            'details' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return $this->json([
        'message' => 'Client enregistré avec succès',
        'client_id' => $client->getId()
    ], Response::HTTP_CREATED);
}
    
    #[Route('/signup', name: 'api_signup', methods: ['POST'])]
   // #[IsGranted('ROLE_ADMIN')]
    public function sigupUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        RoleRepository $roleRepo,
        SluggerInterface $slugger
    ): JsonResponse {
        try {
        // if (!$this->isGranted('ROLE_ADMIN')) {
        //     return new JsonResponse(['message' => 'Accès refusé'], 403);
        // }
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], 400);
        }
        // On récupère le type d'utilisateur
        $userType = $data['user_type'] ?? null;

        if (!$userType) {
            return new JsonResponse(['message' => 'Le type d\'utilisateur est requis'], 400);
        }
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email'] ?? '']);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email déjà utilisé'], 400);
        }
        if (!isset($data['password']) || strlen($data['password']) < 8) {
            return new JsonResponse(['message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
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
        $user->setDateCreation(new \DateTime());
        $user->setIsActive(true); // Définit isActive à true par défaut

        dump($user);
        // Gestion du mot de passe hashé
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Gestion du rôle depuis la BDD
        $roleName = strtoupper($userType); // ADMIN / TECHNICIEN / COMMERCIAL
        $role = $roleRepo->findOneBy(['nom_role' => 'ROLE_' . $roleName]);

        if (!$role) {
            return new JsonResponse(['message' => 'Rôle non trouvé dans la base de données'], 400);
        }

        $user->setRole($role);
        // Gestion de la photo
        if (isset($data['photo']) && !empty($data['photo'])) {
            $photoData = base64_decode($data['photo'], true);
            
            // Vérifier si le décodage a échoué
            if ($photoData === false) {
                return new JsonResponse(['message' => 'Données de l\'image invalides'], 400);
            }
        
            // Vérifier si c'est une image valide
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($photoData);
        
            if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                return new JsonResponse(['message' => 'Format d\'image non valide'], 400);
            }
        
            $photoFilename = $slugger->slug($user->getNom()) . '-' . uniqid() . '.jpg';
            $photoPath = $this->getParameter('user_photos_directory') . '/' . $photoFilename;
            if (file_put_contents($photoPath, $photoData) === false) {
                return new JsonResponse(['message' => 'Erreur lors de l\'enregistrement de la photo'], 500);
            }

            $user->setPhoto($photoFilename);
        }
        
                // On sauvegarde !
        $entityManager->persist($user);
        $entityManager->flush();
        $photoUrl = null;
        if ($user->getPhoto()) {
            $photoUrl = $request->getSchemeAndHttpHost() . '/uploads/users/' . $user->getPhoto();
        }
        return new JsonResponse([
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'photo' => $photoUrl,
                'isActive' => $user->isActive(),

            ]
        ], 201);
       // return new JsonResponse(['message' => 'Utilisateur créé avec succès'], 201);
    } catch (\Exception $e) {
        return new JsonResponse(['message' => 'Une erreur est survenue', 'details' => $e->getMessage()], 500);
    }
    }
    #[Route('/getTechnicien', name: 'api_get_users', methods: ['GET'])]
public function getTechnicien(UserRepository $userRepository): JsonResponse
{
    // Récupérer les utilisateurs ayant le rôle TECHNICIEN
    $roles = ['ROLE_TECHNICIEN'];
    $users = $userRepository->findUsersByRoles($roles);

    // Formater la réponse
    $userList = array_map(function($user) {
        $userData = [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'numTel' => $user->getNumTel(),
            'user_type' => $user instanceof Technicien ? 'TECHNICIEN' : 'UNKNOWN',
            'photo'=> $user->getPhoto(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
            'isActive' => $user->isActive(),
        ];

        // Ajouter les champs spécifiques aux techniciens
        if ($user instanceof Technicien) {
            $userData['disponibilite'] = $user->isDisponibilite();
            $userData['specialite'] = $user->getSpecialite();
        }

        return $userData;
    }, $users);

    return new JsonResponse($userList, 200);
}

#[Route('/updateTechnicien/{id}', name: 'api_update_technicien', methods: ['PUT'])]
public function updateTechnicien(int $id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Find the existing Technicien entity by ID
    $technicien = $userRepository->find($id);

    if (!$technicien) {
        return new JsonResponse(['message' => 'Technicien non trouvé'], 404);
    }

    // Update fields
    if (isset($data['nom'])) {
        $technicien->setNom($data['nom']);
    }
    if (isset($data['prenom'])) {
        $technicien->setPrenom($data['prenom']);
    }
    if (isset($data['email'])) {
        $technicien->setEmail($data['email']);
    }
    if (isset($data['numTel'])) {
        $technicien->setNumTel($data['numTel']);
    }
    if (isset($data['disponibilite'])) {
        $technicien->setDisponibilite($data['disponibilite']);
    }
    if (isset($data['specialite'])) {
        $technicien->setSpecialite($data['specialite']);
    }
    if (isset($data['password'])) {
        $hashedPassword = $passwordHasher->hashPassword($technicien, $data['password']);
        $technicien->setPassword($hashedPassword);
    }
    if (isset($data['photo'])) {
        $technicien->setPhoto($data['photo']);
    }

    // Save the changes
    $userRepository->updateTechnicien($technicien);

    return new JsonResponse(['message' => 'Technicien mis à jour avec succès'], 200);
}


#[Route('/desactiveUser/{id}', name: 'api_delete_technicien', methods: ['DELETE'])]
public function deleteTechnicien(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
{
    // Trouver le technicien par son ID
    $technicien = $userRepository->find($id);

    if (!$technicien) {
        return new JsonResponse(['message' => 'Technicien non trouvé'], 404);
    }

    // Désactiver le compte
    $technicien->setIsActive(false);

    // Sauvegarder les modifications
    $em->persist($technicien);
    $em->flush();

    return new JsonResponse(['message' => 'Compte technicien désactivé avec succès'], 200);
}

#[Route('/activateUser/{id}', name: 'api_activate_technicien', methods: ['PUT'])]
public function activateTechnicien(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
{
    $technicien = $userRepository->find($id);

    if (!$technicien) {
        return new JsonResponse(['message' => 'Technicien non trouvé'], 404);
    }

    $technicien->setIsActive(true);

    $em->persist($technicien);
    $em->flush();

    return new JsonResponse(['message' => 'Compte technicien réactivé avec succès'], 200);
}
#[Route('/getCommercial', name: 'api_get_technicien', methods: ['GET'])]
public function getCommercial(UserRepository $userRepository): JsonResponse
{
    // Récupérer les utilisateurs ayant le rôle TECHNICIEN
    $roles = ['ROLE_COMMERCIAL'];
    $users = $userRepository->findUsersByRoles($roles);

    // Formater la réponse
    $userList = array_map(function($user) {
        $userData = [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'numTel' => $user->getNumTel(),
            'user_type' => $user instanceof Commercial ? 'COMMERCIAL' : 'UNKNOWN',
            'photo'=> $user->getPhoto(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
            'isActive' => $user->isActive(),
        ];

        // Ajouter les champs spécifiques aux techniciens
        if ($user instanceof Commercial) {
            $userData['region'] = $user->getRegion();
           
        }

        return $userData;
    }, $users);

    return new JsonResponse($userList, 200);
}
#[Route('/updateCommercial/{id}', name: 'api_update_Commercial', methods: ['PUT'])]
public function updateCommercial(int $id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Find the existing Technicien entity by ID
    $commercila = $userRepository->find($id);

    if (!$commercila) {
        return new JsonResponse(['message' => 'Commercial non trouvé'], 404);
    }

    // Update fields
    if (isset($data['nom'])) {
        $commercila->setNom($data['nom']);
    }
    if (isset($data['prenom'])) {
        $commercila->setPrenom($data['prenom']);
    }
    if (isset($data['email'])) {
        $commercila->setEmail($data['email']);
    }
    if (isset($data['numTel'])) {
        $commercila->setNumTel($data['numTel']);
    }
    if (isset($data['region'])) {
        $commercila->setRegion($data['region']);
    }
    if (isset($data['photo'])) {
        $commercila->setPhoto($data['photo']);
    }
    if (isset($data['password'])) {
        $hashedPassword = $passwordHasher->hashPassword($commercila, $data['password']);
        $commercila->setPassword($hashedPassword);
    }
   

    // Save the changes
    $userRepository->updateCommercial($commercila);

    return new JsonResponse(['message' => 'Commercial mis à jour avec succès'], 200);
}

// #[Route('/deleteCommercial/{id}', name: 'api_delete_commercial', methods: ['DELETE'])]
// public function deleteCommercial(int $id, UserRepository $userRepository): JsonResponse
// {
//     // Utiliser la méthode deleteTechnicien du UserRepository
//     $userRepository->deleteCommercial($id);

//     return new JsonResponse(['message' => 'Commercial supprimé avec succès'], 200);
// }

}


