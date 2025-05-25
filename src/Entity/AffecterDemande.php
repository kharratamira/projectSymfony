<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AffecterDemandeRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AffecterDemandeRepository::class)]
class AffecterDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePrevu = null;
    #[ORM\Column(type:"string",enumType: StatutAffectation::class)]
    #[Groups(['demande:read', 'client:read'])]
    private ?StatutAffectation $statutAffectation = null;


    #[ORM\ManyToOne(targetEntity: Technicien::class, inversedBy: 'affectations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Technicien $technicien = null;

    
    #[ORM\ManyToOne(targetEntity: DemandeIntervention::class, inversedBy: 'affecterDemandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DemandeIntervention $demande = null;

    #[ORM\OneToOne(mappedBy: 'affectation', targetEntity: Intervention::class, cascade: ['persist', 'remove'])]
    private ?Intervention $intervention = null; 
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAfectation = null;
    public function __construct()
    {
        $this->dateAfectation = new \DateTime(); // Date actuelle
        $this->statutAffectation = StatutAffectation::EN_ATTENTE; // Statut par défaut
       
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrevu(): ?\DateTimeInterface
    {
        return $this->datePrevu;
    }

    public function setDatePrevu(\DateTimeInterface $datePrevu): static
    {
        $this->datePrevu = $datePrevu;

        return $this;
    }

    public function getTechnicien(): ?Technicien
    {
        return $this->technicien;
    }

    public function setTechnicien(?Technicien $technicien): static
    {
        $this->technicien = $technicien;

        return $this;
    }

    public function getDemande(): ?DemandeIntervention
    {
        return $this->demande;
    }

    public function setDemande(?DemandeIntervention $demande): static
    {
        $this->demande = $demande;

        return $this;
    }

    public function getStatutAffectation(): ?StatutAffectation
    {
        return $this->statutAffectation;
    }

    public function setStatutAffectation(StatutAffectation $statutAffectation): static
    {
        $this->statutAffectation = $statutAffectation;
        return $this;
    }

    public function getDateAfectation(): ?\DateTimeInterface
    {
        return $this->dateAfectation;
    }

    public function setDateAfectation(\DateTimeInterface $dateAfectation): static
    {
        $this->dateAfectation = $dateAfectation;

        return $this;
    }
    public function getIntervention(): ?Intervention
{
    return $this->intervention;
}

public function setIntervention(?Intervention $intervention): static
{
    $this->intervention = $intervention;
    return $this;
}
}
