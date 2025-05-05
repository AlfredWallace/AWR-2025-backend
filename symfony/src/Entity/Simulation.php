<?php

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) ?int $id;

    #[ORM\OneToMany(targetEntity: RugbyMatch::class, mappedBy: "simulation", cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["order" => "ASC"])]
    readonly Collection $matches;

    #[ORM\OneToMany(targetEntity: TeamPoints::class, mappedBy: "simulation", cascade: ["persist", "remove"])]
    readonly Collection $teamPoints;

    public function __construct(
        #[ORM\Column(length: 255, nullable: true)]
        readonly ?string $name = null
    ) {
        $this->matches = new ArrayCollection();
        $this->teamPoints = new ArrayCollection();
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