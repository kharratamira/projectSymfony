<?php

use Symfony\Component\HttpFoundation\JsonResponse;
$uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/demandes';

// Vérification si le répertoire existe, sinon on le crée
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Vérification des données Base64
if (isset($data['photo']) && !empty($data['photo'])) {
    // Suppression de la partie data:image/jpeg;base64,
    $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $data['photo']);
    
    // Décodage de l'image
    $photoData = base64_decode($base64Image);
    
    if ($photoData === false) {
        return new JsonResponse(['message' => 'Invalid photo data'], 400);
    }
    
    // Création d'un nom de fichier unique
    $photoFileName = uniqid('demande_') . '.jpg';
    $photoPath = $uploadDir . '/' . $photoFileName;

    // Sauvegarde du fichier sur le serveur
    if (file_put_contents($photoPath, $photoData) === false) {
        return new JsonResponse(['message' => 'Failed to save the photo'], 500);
    }

    // Stockage du nom de fichier dans la base de données
    $user->setPhoto($photoFileName);
} else {
    return new JsonResponse(['message' => 'No photo data provided'], 400);
}

?>