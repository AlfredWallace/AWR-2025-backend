<?php

namespace App\Entity;

use App\Repository\TeamPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamPointsRepository::class)]
#[ORM\UniqueConstraint(
    name: "unique_team_simulation_order", 
    columns: ["team_id", "simulation_id", "order"]
)]
#[ORM\Index(name: "idx_simulation_order", columns: ["simulation_id", "order"])]
class TeamPoints
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private(set) ?int $id = null,
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        readonly ?Team $team = null,
        
        #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "teamPoints")]
        #[ORM\JoinColumn(nullable: false)]
        private(set) ?Simulation $simulation = null,
        
        #[ORM\Column]
        readonly int $order = 0,
        
        #[ORM\Column(type: "float")]
        readonly float $points = 0.0
    ) {
    }
    
    public function setSimulation(?Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }
}