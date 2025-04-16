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
                a.datePrevu,
                a.dateAfectation,
                a.statutAffectation,
                t.nom AS technicienNom,
                t.prenom AS technicienPrenom,
                d.description AS demandeDescription,
                c.adresse AS clientAdresse
            ')
            ->join('a.technicien', 't')
            ->join('a.demande', 'd')
            ->join('d.client', 'c'); // Jointure avec l'entité Client
    
        return $qb->getQuery()->getArrayResult();
    }

}
