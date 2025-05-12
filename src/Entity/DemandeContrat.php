<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\DemandeContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DemandeContratRepository::class)]
class DemandeContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\ManyToOne(inversedBy: 'demandeContrats')]
    private ?Client $client = null;
    #[ORM\Column(type:"string",enumType: StatutDemande::class)]
    #[Groups(['demande:read', 'client:read'])]
    private ?StatutDemande $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAction = null;
#[ORM\OneToOne(mappedBy: 'demandeContrat')]
private ?Contrat $contrat = null;
    #[ORM\Column]
    private ?bool $isGenere = null;
    public function __construct()
    {
        $this->statut = StatutDemande::EN_ATTENTE; // Statut par défaut
        $this->dateDemande = new \DateTime();
        $this->dateAction = new \DateTime();
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

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

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

    
    public function getStatut(): ?StatutDemande
    {
        return $this->statut;
    }

    public function setStatut(StatutDemande $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateAction(): ?\DateTimeInterface
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTimeInterface $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }

    public function isGenere(): ?bool
    {
        return $this->isGenere;
    }

    public function setIsGenere(bool $isGenere): static
    {
        $this->isGenere = $isGenere;

        return $this;
    }
    
public function getContrat(): ?Contrat
{
    return $this->contrat;
}

public function setContrat(?Contrat $contrat): static
{
    $this->contrat = $contrat;
    return $this;
}

}
