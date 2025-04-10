<?php

namespace App\Repository;

use App\Entity\Commercial;
use App\Entity\User;
use App\Entity\Technicien;
use App\Entity\CompteClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, user::class);
        $this->entityManager = $entityManager;
    }
    
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    

    // Custom query method to find a client by email (you can add more custom methods here)
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
//     public function findUsersByRoles(array $roles): array
// {
//     return $this->createQueryBuilder('u')
//         ->innerJoin('u.role', 'r')
//         ->where('r.nom_role IN (:roles)')
//         ->setParameter('roles', $roles)
//         ->orderBy('u.id', 'desc')
//         ->getQuery()
//         ->getResult();
// }
public function findUsersByRoles(array $roles): array
{
    return $this->createQueryBuilder('u')
        ->innerJoin('u.role', 'r')
        ->where('r.nom_role IN (:roles)')
        ->setParameter('roles', $roles)
        ->orderBy('u.id', 'desc')
        ->getQuery()
        ->getResult();
}
public function updateTechnicien(Technicien $updatedTechnicien): void
{
    // Find the existing Technicien entity by ID
    $technicien = $this->find($updatedTechnicien->getId());

    // Check if the entity is found
    if ($technicien) {
        // Update fields
        $technicien->setNom($updatedTechnicien->getNom());
        $technicien->setPrenom($updatedTechnicien->getPrenom());
        $technicien->setEmail($updatedTechnicien->getEmail());
        $technicien->setNumTel($updatedTechnicien->getNumTel());
        $technicien->setDisponibilite($updatedTechnicien->isDisponibilite());
        $technicien->setSpecialite($updatedTechnicien->getSpecialite());
        $technicien->setPhoto($updatedTechnicien->getPhoto());

        // Flush the changes to the database
        $this->entityManager->flush();
    }
}
// public function deleteTechnicien(int $id): void
//     {
//         // Find the existing Technicien entity by ID
//         $technicien = $this->find($id);

//         // Check if the entity is found
//         if ($technicien) {
//             // Remove the entity from the database
//             $this->entityManager->remove($technicien);
//             $this->entityManager->flush();
//         }
//     }

    public function updateCommercial(Commercial $updatedCommercial): void
{
    // Find the existing Technicien entity by ID
    $commercial = $this->find($updatedCommercial->getId());

    // Check if the entity is found
    if ($commercial) {
        // Update fields
        $commercial->setNom($updatedCommercial->getNom());
        $commercial->setPrenom($updatedCommercial->getPrenom());
        $commercial->setEmail($updatedCommercial->getEmail());
        $commercial->setNumTel($updatedCommercial->getNumTel());
        $commercial->setRegion($updatedCommercial->getRegion());
        $commercial->setPhoto($updatedCommercial->getPhoto());
        
        // Flush the changes to the database
        $this->entityManager->flush();
    }
}
// public function deleteCommercial(int $id): void
//     {
//         // Find the existing Technicien entity by ID
//         $commercial = $this->find($id);

//         // Check if the entity is found
//         if ($commercial) {
//             // Remove the entity from the database
//             $this->entityManager->remove($commercial);
//             $this->entityManager->flush();
//         }
//     }

//    /**
//     * @return CompteClient[] Returns an array of CompteClient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CompteClient
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
