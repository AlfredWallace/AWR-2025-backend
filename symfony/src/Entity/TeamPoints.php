<?php

namespace App\Entity;

use App\Repository\TeamPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamPointsRepository::class)]
#[ORM\UniqueConstraint(
    name: "unique_team_simulation_stepNumber", 
    columns: ["team_id", "simulation_id", "step_number"]
)]
#[ORM\Index(name: "idx_simulation_stepNumber", columns: ["simulation_id", "step_number"])]
class TeamPoints
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column(name: "step_number")]
    private(set) int $stepNumber;

    #[ORM\Column(type: "float")]
    private(set) float $points;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Team $team;

    #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "teamPoints")]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Simulation $simulation;

    public function setStepNumber(int $stepNumber): self
    {
        $this->stepNumber = $stepNumber;
        return $this;
    }

    public function setPoints(float $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function setSimulation(Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }
}
