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
            IDENTITY(a.demande) as demande_id,
            a.datePrevu,
            a.statutAffectation,
            t.nom as technicien_nom,
            t.prenom as technicien_prenom
            
        ')
        ->join('a.technicien', 't')
        ->join('a.demande', 'd');
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
public function getAffectationdddd(array $criteria): array
{
    $qb = $this->createQueryBuilder('a')
        ->leftJoin('a.technicien', 't') // jointure avec l'entité Technicien
        ->leftJoin('a.demande', 'd')    // jointure avec la demande si besoin
        ->addSelect('t', 'd');

    if (!empty($criteria['technicien_id'])) {
        $qb->andWhere('t.id = :technicien_id')
           ->setParameter('technicien_id', $criteria['technicien_id']);
    }

    if (!empty($criteria['email'])) {
        $qb->andWhere('t.email = :email')
           ->setParameter('email', $criteria['email']);
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
    public function getAffectationWithDetails(array $criteria): array
{
    $qb = $this->createQueryBuilder('a')
        ->select([
            'a.id',
            'a.datePrevu',
            'a.statutAffectation',
            'a.dateAfectation',
            't.id as technicien_id',
            't.email as technicien_email',
            't.nom as technicien_nom',
            't.prenom as technicien_prenom',
            'd.id as demande_id',
            ///'d.titre as demande_titre'
        ])
        ->join('a.technicien', 't')
        ->join('a.demande', 'd');

    if (!empty($criteria['technicien_id'])) {
        $qb->andWhere('t.id = :technicien_id')
           ->setParameter('technicien_id', $criteria['technicien_id']);
    }

    if (!empty($criteria['email'])) {
        $qb->andWhere('t.email = :email')
           ->setParameter('email', $criteria['email']);
    }

    if (!empty($criteria['date_prevu'])) {
        $qb->andWhere('a.datePrevu = :date_prevu')
           ->setParameter('date_prevu', $criteria['date_prevu']);
    }

    return $qb->getQuery()->getArrayResult();
}
public function findByTechnicienEmail(string $email): array
{
    return $this->createQueryBuilder('a')
        ->select('a', 't', 'd')
        ->join('a.technicien', 't')
        ->join('a.demande', 'd')
        ->where('t.email = :email')
        
        ->setParameter('email', $email)

        ->orderBy('a.datePrevu', 'ASC')
        ->getQuery()
        ->getArrayResult();
}
}
