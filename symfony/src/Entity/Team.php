<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    public function __construct(
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        readonly ?int $id = null,

        #[ORM\Column(length: 255)]
        #[Assert\NotBlank]
        readonly ?string $name = null,

        #[ORM\Column(length: 10)]
        #[Assert\NotBlank]
        private(set) ?string $abbreviation = null,

        #[ORM\Column]
        #[Assert\NotBlank]
        readonly ?string $externalId = null,

        #[ORM\Column]
        #[Assert\NotBlank]
        readonly ?string $externalAltId = null,

        #[ORM\Column(length: 10)]
        #[Assert\NotBlank]
        #[Assert\Length(max: 3)]
        private(set) ?string $countryCode = null,

        #[ORM\Column(type: "float")]
        #[Assert\NotBlank]
        readonly ?float $points = null,

        #[ORM\Column(type: "float")]
        #[Assert\NotBlank]
        readonly ?float $previousPoints = null
    ) {
        $this->abbreviation = strtoupper($abbreviation);
        $this->countryCode = strtoupper($countryCode);
    }
}
