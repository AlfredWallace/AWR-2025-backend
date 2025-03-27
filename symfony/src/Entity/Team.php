<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 10)]
    private string $abbreviation;

    #[ORM\Column]
    private string $externalId;

    #[ORM\Column]
    private string $externalAltId;

    #[ORM\Column(length: 10)]
    private string $countryCode;

    #[ORM\Column(type: "float")]
    private float $points;

    #[ORM\Column(type: "float")]
    private float $previousPoints;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getExternalAltId(): string
    {
        return $this->externalAltId;
    }

    public function setExternalAltId(string $externalAltId): static
    {
        $this->externalAltId = $externalAltId;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getPoints(): float
    {
        return $this->points;
    }

    public function setPoints(float $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPreviousPoints(): float
    {
        return $this->previousPoints;
    }

    public function setPreviousPoints(float $previousPoints): static
    {
        $this->previousPoints = $previousPoints;

        return $this;
    }
}