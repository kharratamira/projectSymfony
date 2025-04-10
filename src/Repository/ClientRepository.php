<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager; 
    public function __construct(ManagerRegistry $registry ,  EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Client::class);
        $this->entityManager = $entityManager;
    }
    public function add(Client $client, bool $flush = true): void
    {
        $this->entityManager->persist($client);// Prépare l'entité pour l'insertion en base de données
        if ($flush) {
            $this->entityManager->flush();// Exécute la transaction et enregistre les modifications
        }
    }
    public function findAllClientWithDemande(): array
    {
        return $this->createQueryBuilder('c')
        ->select('c.id, c.nom, c.prenom, c.numTel, c.email, c.entreprise, c.adresse, c.isActive')
        ->distinct()
        ->getQuery()
        ->getArrayResult();
    }
    public function updateClient(Client $client, array $newData): void
    {
        // Update Client fields
        if (isset($newData['nom'])) {
            $client->setNom($newData['nom']);
        }
        if (isset($newData['prenom'])) {
            $client->setPrenom($newData['prenom']);
        }
        if (isset($newData['email'])) {
            $client->setEmail($newData['email']);
        }
        if (isset($newData['adresse'])) {
            $client->setAdresse($newData['adresse']);
        }
        if (isset($newData['numTel'])) {
            $client->setNumTel($newData['numTel']);
        }

        // Update the related DemandeInterventions (e.g., nomSociete)
        // $demandeInterventions = $client->getDemandeInterventions();
        // foreach ($demandeInterventions as $demande) {
        //     if (isset($newData['nomSociete'])) {
        //         $demande->setNomSociete($newData['nomSociete']);
        //     }
        // }

        // Persist the changes
        $this->_em->flush(); // Flush the changes to the database
    }

    public function remove(Client $client, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($client);
        
        if ($flush) {
            $entityManager->flush();
        }
    }
    

    
    //    /**
//     * @return Client[] Returns an array of Client objects
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

//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
