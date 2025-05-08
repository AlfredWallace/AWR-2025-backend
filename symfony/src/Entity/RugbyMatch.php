<?php

namespace App\Entity;

use App\Repository\RugbyMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RugbyMatchRepository::class)]
#[ORM\Table(name: "rugby_match")]
class RugbyMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    public function __construct(
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        readonly Team $homeTeam,
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        readonly Team $awayTeam,
        
        #[ORM\Column]
        #[Assert\GreaterThanOrEqual(0)]
        readonly int $homeScore,
        
        #[ORM\Column]
        #[Assert\GreaterThanOrEqual(0)]
        readonly int $awayScore,
        
        #[ORM\Column]
        readonly bool $isNeutralGround = false,
        
        #[ORM\Column]
        readonly bool $isWorldCup = false,
        
        #[ORM\Column]
        readonly int $order,
        
        #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "matches")]
        #[ORM\JoinColumn(nullable: false)]
        private(set) Simulation $simulation
    ) {
    }
    
    public function setSimulation(?Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }
    
    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }
}