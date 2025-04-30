<?php

namespace App\Entity;

use App\Repository\AutorisationSortieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AutorisationSortieRepository::class)]
class AutorisationSortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["autorisation:read"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["autorisation:read"])]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["autorisation:read"])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    #[Groups(["autorisation:read"])]
    private ?string $raison = null;

    #[ORM\ManyToOne(targetEntity: Technicien::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["autorisation:read"])]
    private ?Technicien $technicien = null;

    #[ORM\Column(type: "string", enumType: StatutAutorisation::class)]
    #[Groups(["autorisation:read"])]
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

    public function getStatutAutorisation(): StatutAutorisation
    {
        return $this->statutAutorisation;
    }

    public function setStatutAutorisation(StatutAutorisation $statutAutorisation): static
    {
        $this->statutAutorisation = $statutAutorisation;

        return $this;
    }
}