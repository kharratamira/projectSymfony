<?php

namespace App\Entity;

use App\Repository\AutorisationSortieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutorisationSortieRepository::class)]
class AutorisationSortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $raison = null;

    #[ORM\ManyToOne(targetEntity: Technicien::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Technicien $technicien = null;
    #[ORM\Column(type:"string",enumType: StatutAffectation::class)]
    
    private ?StatutAutorisation $statutAutorisation = null;
    public function __construct()
    {
        $this->statutAutorisation = StatutAutorisation::EN_ATTENTE;
    }
        public function getId(): ?int
    {
        return $this->id;
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

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;

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
    public function getStatut(): StatutAutorisation
    {
        return $this->statutAutorisation;
    }

    public function setStatut(StatutAutorisation $statutAutorisation): static
    {
        $this->statutAutorisation = $statutAutorisation;
        return $this;
    }
    
}
