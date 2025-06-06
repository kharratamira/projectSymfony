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
    private ?string $observation = null;

    

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\OneToOne(inversedBy: 'intervention', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?AffecterDemande $affectation = null;
    

    /**
     * @var Collection<int, Tache>
     */
    #[ORM\ManyToMany(targetEntity: Tache::class, mappedBy: 'intervention')]
    private Collection $taches;

   
#[ORM\OneToOne(mappedBy: 'intervention', targetEntity: Facture::class)]
private ?Facture $facture = null;

#[ORM\OneToOne(mappedBy: 'intervention', targetEntity: SatisfactionClient::class)]
private ?SatisfactionClient $satisfactionClient = null;
    public function __construct()
    {
        $this->taches = new ArrayCollection();
    }


public function getFacture(): ?Facture
{
    return $this->facture;
}

public function setFacture(?Facture $facture): static
{
    $this->facture = $facture;
    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->dateFin = $date_fin;

        return $this;
    }

    public function getAffectation(): ?AffecterDemande
    {
        return $this->affectation;
    }

    public function setAffectation(?AffecterDemande $affectation): static
    {
        $this->affectation = $affectation;

        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTach(Tache $tach): static
    {
        if (!$this->taches->contains($tach)) {
            $this->taches->add($tach);
            $tach->addIntervention($this);
        }

        return $this;
    }

    public function removeTach(Tache $tach): static
    {
        if ($this->taches->removeElement($tach)) {
            $tach->removeIntervention($this);
        }

        return $this;
    }

   public function getSatisfactionClient(): ?SatisfactionClient
{
    return $this->satisfactionClient;
}

public function setSatisfactionClient(?SatisfactionClient $satisfactionClient): static
{
    // si nécessaire, lier les deux entités
    if ($satisfactionClient && $satisfactionClient->getIntervention() !== $this) {
        $satisfactionClient->setIntervention($this);
    }

    $this->satisfactionClient = $satisfactionClient;

    return $this;
}
   
    
}
