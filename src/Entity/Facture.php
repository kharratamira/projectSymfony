<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

  #[ORM\Column(length: 255, unique: true)]
    private ?string $numFacture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEmission = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column]
    private ?float $montantHTVA = null;

    #[ORM\Column]
    private ?float $TVA = null;

    #[ORM\Column]
    private ?float $montantTTC = null;
#[ORM\Column(enumType: statutFacture::class)]
private ?statutFacture $statut = statutFacture::EN_ATTENTE;


#[ORM\Column]
private ?float $remise = null;

#[ORM\OneToOne(cascade: ['persist', 'remove'])]
private ?Intervention $intervention = null;

/**
 * @var Collection<int, ModePaiement>
 */
#[ORM\ManyToMany(targetEntity: ModePaiement::class, mappedBy: 'facture')]
private Collection $modePaiements;
#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
private ?\DateTimeInterface $datePaiement = null;


public function __construct()
{
    $this->modePaiements = new ArrayCollection();
}
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(string $numFacture): static
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): static
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(\DateTimeInterface $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    public function getMontantHTVA(): ?float
    {
        return $this->montantHTVA;
    }

    public function setMontantHTVA(float $montantHTVA): static
    {
        $this->montantHTVA = $montantHTVA;

        return $this;
    }

    public function getTVA(): ?float
    {
        return $this->TVA;
    }

    public function setTVA(float $TVA): static
    {
        $this->TVA = $TVA;

        return $this;
    }

    public function getMontantTTC(): ?float
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(float $montantTTC): static
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }
    public function getStatut(): ?statutFacture
{
    return $this->statut;
}

public function setStatut(statutFacture $statut): static
{
    $this->statut = $statut;
    return $this;
}

public function getRemise(): ?float
{
    return $this->remise;
}
public function setRemise(float $remise): static
{
    $this->remise = $remise;

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

/**
 * @return Collection<int, ModePaiement>
 */
public function getModePaiements(): Collection
{
    return $this->modePaiements;
}

public function addModePaiement(ModePaiement $modePaiement): static
{
    if (!$this->modePaiements->contains($modePaiement)) {
        $this->modePaiements->add($modePaiement);
        $modePaiement->addFacture($this);
    }

    return $this;
}

public function removeModePaiement(ModePaiement $modePaiement): static
{
    if ($this->modePaiements->removeElement($modePaiement)) {
        $modePaiement->removeFacture($this);
    }

    return $this;
}
public function getDatePaiement(): ?\DateTimeInterface
{
    return $this->datePaiement;
}

public function setDatePaiement(\DateTimeInterface $datePaiement): static
{
    $this->datePaiement = $datePaiement;
    return $this;
}
}
