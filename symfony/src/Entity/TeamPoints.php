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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    public function __construct(
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        readonly Team $team,
        
        #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "teamPoints")]
        #[ORM\JoinColumn(nullable: false)]
        private(set) Simulation $simulation,
        
        #[ORM\Column]
        readonly int $order,
        
        #[ORM\Column(type: "float")]
        readonly float $points
    ) {
    }
    
    public function setSimulation(Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }
}