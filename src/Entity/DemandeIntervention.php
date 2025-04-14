<?php

namespace App\Entity;
use App\Entity\StatutDemande;
use App\Repository\DemandeInterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeInterventionRepository::class)]
class DemandeIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column(length: 255)]
    // private ?string $nomSociete = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type:"string",enumType: StatutDemande::class)]
    private ?StatutDemande $statut = null;

    #[ORM\ManyToOne(inversedBy: 'demandeInterventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $actionDate = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $photo1 = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $photo2 = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $photo3 = null;

    public function __construct()
    {
        $this->actionDate = new \DateTime(); // Date actuelle
        $this->dateDemande = new \DateTime(); // Date actuelle
        $this->statut = StatutDemande::EN_ATTENTE; // Statut par défaut
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

    public function getStatut(): ?StatutDemande
    {
        return $this->statut;
    }

    public function setStatut(StatutDemande $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
    

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getActionDate(): ?\DateTimeInterface
    {
        return $this->actionDate;
    }

    public function setActionDate(\DateTimeInterface $actionDate): static
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    public function getPhoto1(): ?string
    {
        return $this->photo1;
    }

    public function setPhoto1(?string $photo1): static
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function getPhoto2(): ?string
    {
        return $this->photo2;
    }

    public function setPhoto2(?string $photo2): static
    {
        $this->photo2 = $photo2;

        return $this;
    }

    public function getPhoto3(): ?string
    {
        return $this->photo3;
    }

    public function setPhoto3(?string $photo3): static
    {
        $this->photo3 = $photo3;

        return $this;
    }
}
