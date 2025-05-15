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
    if (!empty($criteria['statuts'])) {
        $qb->andWhere('a.statutAffectation IN (:statuts)')
           ->setParameter('statuts', $criteria['statuts']);
    }

    return $qb->getQuery()->getArrayResult();
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
            'd.description as demande_description',
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
