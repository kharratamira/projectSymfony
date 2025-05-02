<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }
    
public function findAllTaches(): array
{
    return $this->createQueryBuilder('t')
        ->select('t.id, t.tache, t.prixTache')
        ->getQuery()
        ->getArrayResult();
}
}