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

   

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\OneToOne(inversedBy: 'contrat',cascade: ['persist', 'remove'])]
    private ?DemandeContrat $demandeContrat = null;

   #[ORM\Column(type: 'string', length: 10, unique: true)]
private ?string $NumContrat = null;
 #[ORM\Column(type:"string",enumType: StatutDemande::class)]
      private ?StatutDemande $statutContrat = null;

#[ORM\Column(type:"string",enumType: VieContrat::class)]
      private ?VieContrat $vieContrat = null;
    public function __construct()
    {
       
        $this->statutContrat = StatutDemande::EN_ATTENTE; // Statut par défaut
    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumContrat(): ?string
    {
        return $this->NumContrat;
    }

    public function setNumContrat(string  $NumContrat): static
    {
        $this->NumContrat = $NumContrat;

        return $this;
    }
     public function getStatutContart(): ?StatutDemande
    {
        return $this->statutContrat;
    }

    public function setStatutContrat(StatutDemande $statut): static
    {
        $this->statutContrat = $statut;
        switch ($statut) {
        case StatutDemande::Accepter:
            $this->vieContrat = VieContrat::ACTIVE;
            break;
        case StatutDemande::ANNULEE:
            $this->vieContrat=VieContrat::ANNULEE;
            break;
        case StatutDemande::EN_ATTENTE:
            $this->vieContrat = null; // ou VieContrat::EXPIRE ou rien du tout selon ton besoin
            break;
    }
    
        return $this;
    }
     public function getVieContart(): ?VieContrat
    { $today = new \DateTime();
    if ($this->dateFin !== null && $today > $this->dateFin) {
        return VieContrat::EXPIRE;
    }
        return $this->vieContrat;
    }

    public function setVieContrat(VieContrat $vieContrat): static
    {
        $this->vieContrat = $vieContrat;
        
        return $this;
    }
public function updateVieContrat(): void
{
    $today = new \DateTime();

    if ($this->getDateFin() < $today) {
        $this->vieContrat = VieContrat::EXPIRE;
    } elseif ($this->getStatutContart() === StatutDemande::ANNULEE) {
        $this->vieContrat = VieContrat::ANNULEE;
    } elseif ($this->getStatutContart() === StatutDemande::EN_ATTENTE) {
        $this->vieContrat = VieContrat::EN_ATTENTE;
    } else {
        $this->vieContrat = VieContrat::ACTIVE;
    }
}

}
