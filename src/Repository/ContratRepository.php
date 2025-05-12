<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Contrat;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }

 public function findAllContrat(?User $user = null)
{
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.demandeContrat', 'd')
        ->leftJoin('d.client', 'cl')
        ->addSelect('d', 'cl')
         ->andWhere('c.statutContrat = :statut')
        ->setParameter('statut', 'accepter')
        ->orderBy('d.dateDemande', 'DESC');

    // Filtre pour les clients (non admin)
    if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
        $qb->andWhere('cl.id = :clientId')
           ->setParameter('clientId', $user->getId());
    }

    return $qb->getQuery()->getResult();
}
public function findContratsByEmail(string $email, string $role): array
{
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.demandeContrat', 'd')
        ->leftJoin('d.client', 'cl')
        ->addSelect('d', 'cl')
        ->orderBy('d.dateDemande', 'DESC');

    if ($role === 'ROLE_CLIENT') {
        $qb->where('cl.email = :email')
        ->andWhere('c.statutContrat = :statut')
           ->setParameter('statut', 'accepter');
    } else {
        throw new \InvalidArgumentException('Rôle non valide.');
    }

    return $qb
        ->setParameter('email', $email)
        ->orderBy('d.id', 'DESC')
        ->getQuery()
        ->getResult(); // retourne des objets DemandeContrat
}
}