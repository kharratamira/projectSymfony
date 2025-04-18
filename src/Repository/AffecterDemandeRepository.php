<?php

namespace App\Repository;

use App\Entity\AffecterDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffecterDemande>
 */
class AffecterDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffecterDemande::class);
    }
    
    // public function getAffectation(array $criteria = []): array
    // {
    //     $qb = $this->createQueryBuilder('a')
    //         ->select('
    //             a.id,
    //             a.datePrevu,
    //             a.dateAfectation,
    //             a.statutAffectation,
    //             t.nom AS technicienNom,
    //             t.prenom AS technicienPrenom,
    //             d.description AS demandeDescription,
    //             c.adresse AS clientAdresse
    //         ')
    //         ->join('a.technicien', 't')
    //         ->join('a.demande', 'd')
    //         ->join('d.client', 'c'); // Jointure avec l'entité Client
    
    //     return $qb->getQuery()->getArrayResult();
    // }
    // Dans AffecterDemandeRepository.php
public function getAffectation(array $criteria = []): array
{
    $qb = $this->createQueryBuilder('a')
        ->select('
            a.id,
            IDENTITY(a.technicien) as technicien_id,
            a.datePrevu,
            t.nom as technicien_nom,
            t.prenom as technicien_prenom
        ')
        ->join('a.technicien', 't');
    
    // Ajouter des filtres si des critères sont fournis
    if (!empty($criteria['technicien_id'])) {
        $qb->andWhere('a.technicien = :technicien_id')
           ->setParameter('technicien_id', $criteria['technicien_id']);
    }
    
    if (!empty($criteria['date_prevu'])) {
        $qb->andWhere('a.datePrevu = :date_prevu')
           ->setParameter('date_prevu', $criteria['date_prevu']);
    }

    return $qb->getQuery()->getArrayResult();
}
    public function findTechnicienAvailability(int $technicienId, \DateTimeInterface $datePrevu): bool
    {
        $result = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.technicien = :technicienId')
            ->andWhere('a.datePrevu = :datePrevu')
            ->setParameter('technicienId', $technicienId)
            ->setParameter('datePrevu', $datePrevu)
            ->getQuery()
            ->getSingleScalarResult();
    
        return $result === 0;
    }
}
