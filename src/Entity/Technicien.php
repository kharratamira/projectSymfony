<?php

namespace App\Entity;

use App\Repository\TechnicienRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechnicienRepository::class)]
class Technicien extends User
{

    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\Column(length: 255)]
    private ?string $specialite = null;

    

    /**
     * @var Collection<int, AffecterDemande>
     */
    
     #[ORM\OneToMany(targetEntity: AffecterDemande::class, mappedBy: 'technicien')]
     private Collection $affectations;
    public function __construct()
    {
        parent::__construct();
        
        $this->affectations = new ArrayCollection();
    }

   

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

   
    
   
    /**
     * @return Collection<int, AffecterDemande>
     */
    public function getTechnicien(): Collection
    {
        return $this->affectations;
    }

    public function addTechnicien(AffecterDemande $technicien): static
    {
        if (!$this->affectations->contains($technicien)) {
            $this->affectations->add($technicien);
            $technicien->setTechnicien($this);
        }

        return $this;
    }

    public function removeTechnicien(AffecterDemande $technicien): static
    {
        if ($this->affectations->removeElement($technicien)) {
            // set the owining side to null (unless already changed)
            if ($technicien->getTechnicien() === $this) {
                $technicien->setTechnicien(null);
            }
        }

        return $this;
    }
}
