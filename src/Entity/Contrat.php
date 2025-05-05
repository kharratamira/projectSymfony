<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   
    #[ORM\Column(length: 255)]
    private ?string $descriptionContrat = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?DemandeContrat $demandeContrat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getDescriptionContrat(): ?string
    {
        return $this->descriptionContrat;
    }

    public function setDescriptionContrat(string $descriptionContrat): static
    {
        $this->descriptionContrat = $descriptionContrat;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDemandeContrat(): ?DemandeContrat
    {
        return $this->demandeContrat;
    }

    public function setDemandeContrat(?DemandeContrat $demandeContrat): static
    {
        $this->demandeContrat = $demandeContrat;

        return $this;
    }
}
