<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api')]
class SecurityController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route(path: '/login_client', name: 'app_login_client', methods: ['POST'])]
public function login(Request $request): JsonResponse
{
    // Get the login data (email and password)
    $data = json_decode($request->getContent(), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        return $this->json(['error' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Find the client by email
    $client = $this->entityManager->getRepository(User::class)->findOneByEmail($email);

    // If no client is found with the provided email
    if (!$client) {
        return $this->json(['error' => 'Account does not exist'], JsonResponse::HTTP_UNAUTHORIZED);
    }
    if (!$client->isActive()) {
        return $this->json(['error' => 'Account is not active'], JsonResponse::HTTP_FORBIDDEN);
    }
    // Check if the password is valid using the password hasher
    if (!$this->passwordHasher->isPasswordValid($client, $password)) {
        return $this->json(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
    }
    ///////////////////
    $session = $request->getSession();
    $session->set('user_nom', $client->getNom());
    $session->set('user_prenom', $client->getPrenom());
    $session->set('user_roles', $client->getRoles());
    // Create a new token for the authenticated client
    $token = new UsernamePasswordToken($client, 'user_main', $client->getRoles());
    $this->tokenStorage->setToken($token);

    // Return the success message
    return $this->json([
        'message' => 'Logged in successfully',
        'user' => [
            'id' => $client->getId(),
            'email' => $client->getEmail(),
            'nom'=>$client->getNom(),
            'prenom'=>$client->getPrenom(),
            'photo' => $client->getPhoto()
        ],
        'roles' => $client->getRoles()
    ]);
}
    #[Route(path: '/logout', name: 'api_logout_client')]
    public function logout(): void
    {
        // This method will be intercepted by the Symfony firewall automatically
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/sessionUser', name: 'api_get_user', methods: ['GET'])]
public function getUserInfo(Request $request): JsonResponse
{
    $session = $request->getSession();
    $nom = $session->get('user_nom');
    $prenom = $session->get('user_prenom');
    $roles = $session->get('user_roles');

    if (!$nom || !$prenom || !$roles) {
        return $this->json(['error' => 'User not logged in'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    return $this->json([
        'nom' => $nom,
        'prenom' => $prenom,
        'roles' => $roles
    ]);
}
// #[Route('/sessionUser', name: 'api_get_user', methods: ['GET'])]
// public function getUserInfo(Request $request): JsonResponse
// {
//     $user = $this->getUser(); // Récupère l'utilisateur actuel
//     return $this->json(['authenticated' => $user !== null]);
// }

}
