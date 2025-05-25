<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column]
    private(set) string $externalId;

    #[ORM\Column]
    private(set) string $externalAltId;

    #[ORM\Column]
    private(set) string $name;

    #[ORM\Column(length: 10)]
    private(set) string $abbreviation;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 3)]
    private(set) string $countryCode;

    #[ORM\Column(type: "float")]
    private(set) float $points;

    #[ORM\Column(type: "float")]
    private(set) float $previousPoints;

    #[ORM\OneToMany(targetEntity: TeamPoints::class, mappedBy: "team")]
    private(set) Collection $teamPoints;

    #[ORM\OneToMany(targetEntity: RugbyMatch::class, mappedBy: "homeTeam")]
    private(set) Collection $homeMatches;

    #[ORM\OneToMany(targetEntity: RugbyMatch::class, mappedBy: "awayTeam")]
    private(set) Collection $awayMatches;

    public function __construct()
    {
        $this->teamPoints = new ArrayCollection();
        $this->homeMatches = new ArrayCollection();
        $this->awayMatches = new ArrayCollection();
    }

    public function setExternalId(string $externalId): Team
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function setExternalAltId(string $externalAltId): Team
    {
        $this->externalAltId = $externalAltId;
        return $this;
    }

    public function setName(string $name): Team
    {
        $this->name = $name;
        return $this;
    }

    public function setAbbreviation(string $abbreviation): Team
    {
        $this->abbreviation = strtoupper($abbreviation);
        return $this;
    }

    public function setCountryCode(string $countryCode): Team
    {
        $this->countryCode = strtoupper($countryCode);
        return $this;
    }

    public function setPoints(float $points): Team
    {
        $this->points = $points;
        return $this;
    }

    public function setPreviousPoints(float $previousPoints): Team
    {
        $this->previousPoints = $previousPoints;
        return $this;
    }

    public function addTeamPoint(TeamPoints $teamPoint): self
    {
        if (!$this->teamPoints->contains($teamPoint)) {
            $this->teamPoints->add($teamPoint);
            $teamPoint->setTeam($this);
        }

        return $this;
    }

    public function addHomeMatch(RugbyMatch $match): self
    {
        if (!$this->homeMatches->contains($match)) {
            $this->homeMatches->add($match);
            $match->setHomeTeam($this);
        }

        return $this;
    }

    public function addAwayMatch(RugbyMatch $match): self
    {
        if (!$this->awayMatches->contains($match)) {
            $this->awayMatches->add($match);
            $match->setAwayTeam($this);
        }

        return $this;
    }
}
