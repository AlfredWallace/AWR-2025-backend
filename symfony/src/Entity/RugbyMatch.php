<?php

namespace App\Entity;

use App\Repository\RugbyMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RugbyMatchRepository::class)]
#[ORM\Table(name: "rugby_match")]
class RugbyMatch
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private(set) ?int $id = null,
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        #[Assert\NotNull]
        readonly ?Team $homeTeam = null,
        
        #[ORM\ManyToOne(targetEntity: Team::class)]
        #[ORM\JoinColumn(nullable: false)]
        #[Assert\NotNull]
        readonly ?Team $awayTeam = null,
        
        #[ORM\Column]
        #[Assert\GreaterThanOrEqual(0)]
        readonly int $homeScore = 0,
        
        #[ORM\Column]
        #[Assert\GreaterThanOrEqual(0)]
        readonly int $awayScore = 0,
        
        #[ORM\Column]
        readonly bool $isWorldCup = false,
        
        #[ORM\Column]
        readonly int $order = 0,
        
        #[ORM\ManyToOne(targetEntity: Simulation::class, inversedBy: "matches")]
        #[ORM\JoinColumn(nullable: false)]
        private(set) ?Simulation $simulation = null
    ) {
    }
    
    public function setSimulation(?Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }
}