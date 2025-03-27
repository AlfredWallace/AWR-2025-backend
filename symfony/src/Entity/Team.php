<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private(set) ?string $name = null {
        set (?string $name) {
            $this->name = $name;
        }
    }

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    private(set) ?string $abbreviation = null {
        set (?string $abbreviation) {
            $this->abbreviation = $abbreviation;
        }
    }

    #[ORM\Column]
    #[Assert\NotBlank]
    private(set) ?string $externalId = null {
        set (?string $externalId) {
            $this->externalId = $externalId;
        }
    }

    #[ORM\Column]
    #[Assert\NotBlank]
    private(set) ?string $externalAltId = null {
        set (?string $externalAltId) {
            $this->externalAltId = $externalAltId;
        }
    }

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    private(set) ?string $countryCode = null {
        set (?string $countryCode) {
            $this->countryCode = $countryCode;
        }
    }

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank]
    private(set) ?float $points = null {
        set (?float $points) {
            $this->points = $points;
        }
    }

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank]
    private(set) ?float $previousPoints = null {
        set (?float $previousPoints) {
            $this->previousPoints = $previousPoints;
        }
    }
}