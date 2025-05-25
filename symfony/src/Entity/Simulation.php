<?php

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column]
    private(set) string $name;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "simulations")]
    #[ORM\JoinColumn(nullable: false)]
    private(set) User $user;

    #[ORM\OneToMany(targetEntity: RugbyMatch::class, mappedBy: "simulation", cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["stepNumber" => "ASC"])]
    #[Serializer\Ignore]
    private(set) Collection $matches;

    #[ORM\OneToMany(targetEntity: TeamPoints::class, mappedBy: "simulation", cascade: ["persist", "remove"])]
    #[Serializer\Ignore]
    private(set) Collection $teamPoints;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
        $this->teamPoints = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function addMatch(RugbyMatch $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches->add($match);
            $match->setSimulation($this);
        }

        return $this;
    }

    public function addTeamPoint(TeamPoints $teamPoint): self
    {
        if (!$this->teamPoints->contains($teamPoint)) {
            $this->teamPoints->add($teamPoint);
            $teamPoint->setSimulation($this);
        }

        return $this;
    }
}
