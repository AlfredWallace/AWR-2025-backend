<?php

namespace App\Entity;

use App\Repository\RugbyMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RugbyMatchRepository::class)]
#[ORM\Table(name: "rugby_match")]
#[ORM\UniqueConstraint(
    name: "unique_simulation_stepNumber", 
    columns: ["simulation_id", "step_number"]
)]
#[ORM\Index(name: "idx_simulation_stepNumber", columns: ["simulation_id", "step_number"])]
class RugbyMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    private(set) int $homeScore;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    private(set) int $awayScore;

    #[ORM\Column(name: "step_number")]
    private(set) int $stepNumber;

    #[ORM\Column]
    private(set) bool $isNeutralGround = false;

    #[ORM\Column]
    private(set) bool $isWorldCup = false;

    #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "matches")]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Simulation $simulation;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Team $homeTeam;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private(set) Team $awayTeam;

    public function setHomeScore(int $homeScore): self
    {
        $this->homeScore = $homeScore;
        return $this;
    }

    public function setAwayScore(int $awayScore): self
    {
        $this->awayScore = $awayScore;
        return $this;
    }

    public function setStepNumber(int $stepNumber): self
    {
        $this->stepNumber = $stepNumber;
        return $this;
    }

    public function setIsNeutralGround(bool $isNeutralGround): self
    {
        $this->isNeutralGround = $isNeutralGround;
        return $this;
    }

    public function setIsWorldCup(bool $isWorldCup): self
    {
        $this->isWorldCup = $isWorldCup;
        return $this;
    }

    public function setSimulation(Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }

    public function setHomeTeam(Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;
        return $this;
    }

    public function setAwayTeam(Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;
        return $this;
    }
}
