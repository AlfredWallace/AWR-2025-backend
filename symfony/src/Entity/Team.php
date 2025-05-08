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
    private(set) int $id;

    public function __construct(
        #[ORM\Column]
        readonly string $name,

        #[ORM\Column(length: 10)]
        private(set) string $abbreviation,

        #[ORM\Column]
        readonly string $externalId,

        #[ORM\Column]
        readonly string $externalAltId,

        #[ORM\Column(length: 10)]
        #[Assert\Length(max: 3)]
        private(set) string $countryCode,

        #[ORM\Column(type: "float")]
        readonly float $points,

        #[ORM\Column(type: "float")]
        readonly float $previousPoints
    ) {
        $this->abbreviation = strtoupper($abbreviation);
        $this->countryCode = strtoupper($countryCode);
    }
}
