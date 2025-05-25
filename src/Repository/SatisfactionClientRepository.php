<?php

namespace App\Repository;

use App\Entity\SatisfactionClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SatisfactionClient>
 */
class SatisfactionClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SatisfactionClient::class);
    }

// App\Repository\SatisfactionClientRepository.php
public function findAllWithDetails()
{
    return $this->createQueryBuilder('s')
        ->addSelect('i', 'd', 'c')
        ->join('s.intervention', 'i')
        ->join('i.demande', 'd') // Nom le plus courant
        ->join('d.client', 'c')
        ->getQuery()
        ->getResult();
}
public function findAllsatisfactionClient(): array
{
    return $this->createQueryBuilder('s')
        ->select(
            's.id AS satisfaction_id',
            's.niveau AS satisfaction_niveau',
            's.commentaire AS satisfaction_commentaire',
            's.dateCreation AS satisfaction_date_creation',
            'i.id AS intervention_id',
            'i.dateFin AS intervention_date_fin',
            'i.observation AS intervention_observation',
            'a.id AS affectation_id',
            'a.datePrevu AS affectation_date_prevu', // Correction ici

            'd.id AS demande_id',
            'd.description AS demande_description',
            'c.id AS client_id',
            'c.entreprise AS client_entreprise',
            'c.nom AS client_nom',
            'c.prenom AS client_prenom',
            'c.email AS client_email',
            't.id AS technicien_id',
            't.nom AS technicien_nom',
            't.prenom AS technicien_prenom',
            't.specialite AS technicien_specialite'
        )
        ->join('s.intervention', 'i')
        ->join('i.affectation', 'a')
        ->join('a.demande', 'd')
        ->join('d.client', 'c')
        ->join('a.technicien', 't')
        ->orderBy('s.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}}