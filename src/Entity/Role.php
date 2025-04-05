<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $nom_role = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: User::class)]
    private Collection $users;

    /**
     * @var Collection<int, Premission>
     */
    #[ORM\ManyToMany(targetEntity: Premission::class, mappedBy: 'roles')]
    private Collection $premissions;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->premissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomRole(): ?string
    {
        return $this->nom_role;
    }

    public function setNomRole(string $nom_role): static
    {
        $this->nom_role = $nom_role;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setRole($this); // Relation bidirectionnelle
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->setRole(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, Premission>
     */
    public function getPremissions(): Collection
    {
        return $this->premissions;
    }

    public function addPremission(Premission $premission): static
    {
        if (!$this->premissions->contains($premission)) {
            $this->premissions->add($premission);
            $premission->addRole($this);
        }

        return $this;
    }

    public function removePremission(Premission $premission): static
    {
        if ($this->premissions->removeElement($premission)) {
            $premission->removeRole($this);
        }

        return $this;
    }
}