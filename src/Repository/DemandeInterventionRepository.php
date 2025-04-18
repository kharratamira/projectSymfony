<?php

namespace App\Repository;

use App\Entity\DemandeIntervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeIntervention>
 */
class DemandeInterventionRepository extends ServiceEntityRepository
{ private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, DemandeIntervention::class);
        $this->entityManager = $entityManager;
    }
    public function add(DemandeIntervention $demande, bool $flush = true): void
    {
        $this->entityManager->persist($demande);
        if ($flush) {
            $this->entityManager->flush();
        }
    }
    public function findAllDemande(): array
    {
        // Create a query builder instance to build the custom query
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.client', 'c') // Join the client entity
            ->select('d', 'c') // Select relevant fields
            ->orderBy('d.id', 'DESC'); // Order by date of the request

        // Get the results as an array of DemandeIntervention entities
        $demandes = $qb->getQuery()->getResult();

        return $demandes;
        
        // return $this->createQueryBuilder('d')
        // ->select('d.id,c.client_id, c.entreprise,d.adresse,d.description,d.statut,d.dateDemande')
        // //->leftJoin('c.demandeInterventions', 'd') // Jointure avec DemandeIntervention
        // ->distinct()
        // ->getQuery()
        // ->getResult();
    }
    public function findByClientEmail(string $email): array
{
    return $this->createQueryBuilder('d')
        ->join('d.client', 'c')
        ->where('c.email = :email')
        ->setParameter('email', $email)
        ->orderBy('d.dateDemande', 'DESC') 
        ->getQuery()
        ->getResult();
}
    public function updateDemande(DemandeIntervention $updatedDemande): void
    {
        // Find the existing DemandeIntervention entity by ID
        $demande = $this->find($updatedDemande->getId());

        // Check if the entity is found
        if ($demande) {
            // Update fields
            //$demande->setNomSociete($updatedDemande->getNomSociete());
            $demande->setDescription($updatedDemande->getDescription());
            $demande->setStatut($updatedDemande->getStatut());

            // If the client data has been updated
            if ($updatedDemande->getClient()) {
                $demande->getClient()->setAdresse($updatedDemande->getClient()->getAdresse());
            }

            // Flush the changes to the database
            $this->entityManager->flush();
        }
    }
    public function remove(DemandeIntervention $demande, bool $flush = true): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($demande);
        
        if ($flush) {
            $entityManager->flush();
        }
    }
    public function findDemandesByClientEmail(string $email): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.client', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
            //->orderBy('d.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
//    /**
//     * @return DemandeIntervention[] Returns an array of DemandeIntervention objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DemandeIntervention
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

