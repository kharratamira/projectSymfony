<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

public function findFacturesByClientEmail(string $email): array
{
    return $this->createQueryBuilder('f')
        ->join('f.intervention', 'i')
        ->join('i.affectation', 'a')
        ->join('a.demande', 'd')
        ->join('d.client', 'c')
        ->andWhere('c.email = :email')
        ->setParameter('email', $email)
        ->getQuery()
        ->getResult();
}

}
