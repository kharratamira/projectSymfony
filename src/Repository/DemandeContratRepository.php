<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\DemandeContrat;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<DemandeContrat>
 */
class DemandeContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeContrat::class);
    }

    public function findAllDemande(?User $user = null)
{
    $qb = $this->createQueryBuilder('d')
               ->leftJoin('d.client', 'c')
               ->addSelect('c')
               ->orderBy('d.dateDemande', 'DESC');

    // Si l'utilisateur est un Client (et pas admin)
    if ($user && !in_array('ROLE_ADMIN', $user->getRoles()) && $user instanceof Client) {
        $qb->andWhere('c.id = :clientId')
           ->setParameter('clientId', $user->getId());
    }

    return $qb->getQuery()->getResult();
}
public function findDemandeContratByEmail(string $email, string $role): array
{
    $queryBuilder = $this->createQueryBuilder('d')
        ->join('d.client', 'c');

    if ($role === 'ROLE_CLIENT') {
        $queryBuilder->where('c.email = :email');
    } else {
        throw new \InvalidArgumentException('Rôle non valide.');
    }

    return $queryBuilder
        ->setParameter('email', $email)
        ->orderBy('d.id', 'DESC')
        ->getQuery()
        ->getResult(); // retourne des objets DemandeContrat
}
}