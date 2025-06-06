<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends User
{
    

    

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['client:read', 'demande:read'])]
    private ?string $adresse = null;

   
    /**
     * @var Collection<int, DemandeIntervention>
     */
    #[ORM\OneToMany(mappedBy: "client", targetEntity: DemandeIntervention::class, cascade: ["persist", "remove"])]
    #[Groups(['client:read'])]
    private Collection $demandeInterventions;

   

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['client:read', 'demande:read'])]
    private ?string $entreprise = null;

    /**
     * @var Collection<int, DemandeContrat>
     */
    #[ORM\OneToMany(targetEntity: DemandeContrat::class, mappedBy: 'client')]
    private Collection $demandeContrats;

   
    
    public function __construct()
    {
        $this->demandeInterventions = new ArrayCollection();
        $this->demandeContrats = new ArrayCollection();
    }

    

    

   

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

   

    /**
     * @return Collection<int, DemandeIntervention>
     */
    public function getDemandeInterventions(): Collection
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): static
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->add($demandeIntervention);
            $demandeIntervention->setClient($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): static
    {
        if ($this->demandeInterventions->removeElement($demandeIntervention)) {
            // set the owning side to null (unless already changed)
            if ($demandeIntervention->getClient() === $this) {
                $demandeIntervention->setClient(null);
            }
        }

        return $this;
    }

   

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(string $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * @return Collection<int, DemandeContrat>
     */
    public function getDemandeContrats(): Collection
    {
        return $this->demandeContrats;
    }

    public function addDemandeContrat(DemandeContrat $demandeContrat): static
    {
        if (!$this->demandeContrats->contains($demandeContrat)) {
            $this->demandeContrats->add($demandeContrat);
            $demandeContrat->setClient($this);
        }

        return $this;
    }

    public function removeDemandeContrat(DemandeContrat $demandeContrat): static
    {
        if ($this->demandeContrats->removeElement($demandeContrat)) {
            // set the owning side to null (unless already changed)
            if ($demandeContrat->getClient() === $this) {
                $demandeContrat->setClient(null);
            }
        }

        return $this;
    }

   


    
    
    
}
