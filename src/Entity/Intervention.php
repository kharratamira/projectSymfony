<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $observation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_prevu_intervention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_reele_intervention = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?demandeIntervention $demande = null;

    /**
     * @var Collection<int, Technicien>
     */
    #[ORM\ManyToMany(targetEntity: Technicien::class, inversedBy: 'interventions')]
    private Collection $technicien;

    public function __construct()
    {
        $this->technicien = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): static
    {
        $this->observation = $observation;

        return $this;
    }

    public function getDatePrevuIntervention(): ?\DateTimeInterface
    {
        return $this->date_prevu_intervention;
    }

    public function setDatePrevuIntervention(\DateTimeInterface $date_prevu_intervention): static
    {
        $this->date_prevu_intervention = $date_prevu_intervention;

        return $this;
    }

    public function getDateReeleIntervention(): ?\DateTimeInterface
    {
        return $this->date_reele_intervention;
    }

    public function setDateReeleIntervention(\DateTimeInterface $date_reele_intervention): static
    {
        $this->date_reele_intervention = $date_reele_intervention;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getDemande(): ?demandeIntervention
    {
        return $this->demande;
    }

    public function setDemande(?demandeIntervention $demande): static
    {
        $this->demande = $demande;

        return $this;
    }

    /**
     * @return Collection<int, Technicien>
     */
    public function getTechnicien(): Collection
    {
        return $this->technicien;
    }

    public function addTechnicien(Technicien $technicien): static
    {
        if (!$this->technicien->contains($technicien)) {
            $this->technicien->add($technicien);
        }

        return $this;
    }

    public function removeTechnicien(Technicien $technicien): static
    {
        $this->technicien->removeElement($technicien);

        return $this;
    }
}
