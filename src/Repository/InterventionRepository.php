<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    
public function findAllInterventions(): array
{
    return $this->createQueryBuilder('i')
        ->select(
            'i.id AS intervention_id',
            'i.dateFin AS intervention_date_fin',
            'i.observation AS intervention_observation',
            'a.datePrevu AS affectation_date_prevu',
            'd.id AS demande_id',
            'd.description AS demande_description',
            'c.entreprise AS client_entreprise',
            'c.nom AS client_nom',
            'c.prenom AS client_prenom',
            't.nom AS technicien_nom',
            't.prenom AS technicien_prenom'
        )
        ->join('i.affectation', 'a')
        ->join('a.demande', 'd')
        ->join('d.client', 'c')
        ->join('a.technicien', 't')
        ->orderBy('i.dateFin', 'DESC') // Trier par date de fin décroissante
        ->getQuery()
        ->getResult();
}

public function findInterventionsByClientEmail(string $email): array
{
    return $this->createQueryBuilder('i')
        ->select(
            'i.id AS intervention_id',
            'i.dateFin AS intervention_date_fin',
            'i.observation AS intervention_observation',
            'a.datePrevu AS affectation_date_prevu',
            'd.id AS demande_id',
            'd.description AS demande_description',
            'c.entreprise AS client_entreprise',
            'c.nom AS client_nom',
            'c.prenom AS client_prenom',
            't.nom AS technicien_nom',
            't.prenom AS technicien_prenom'
        )
        ->join('i.affectation', 'a')
        ->join('a.demande', 'd')
        ->join('d.client', 'c')
        ->join('a.technicien', 't')
        ->where('c.email = :email')
        ->setParameter('email', $email)
        ->orderBy('i.dateFin', 'DESC') // Trier par date de fin décroissante
        ->getQuery()
        ->getResult();
}
}