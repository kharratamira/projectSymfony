<?php

namespace App\Entity;

use App\Repository\PremissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PremissionRepository::class)]
class Premission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_premission = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'premissions')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomPremission(): ?string
    {
        return $this->nom_premission;
    }

    public function setNomPremission(string $nom_premission): static
    {
        $this->nom_premission = $nom_premission;

        return $this;
    }

    /**
     * @return Collection<int, role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(role $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }
}
