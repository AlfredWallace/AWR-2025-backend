<?php

namespace App\Service;

use App\Entity\Simulation;
use App\Entity\TeamPoints;
use App\Exception\InvalidSimulationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class SimulationRunner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PointsExchangeCalculator $pointsCalculator,
    ) {
    }

    public function runNewSimulation(Simulation $simulation): void
    {
        $this->checkIfItHasMatches($simulation);

        // Check if the simulation has rugby matches
        if (!$simulation->teamPoints->isEmpty()) {
            throw new InvalidSimulationException('Simulation already has team points.');
        }

        $this->run($simulation);
    }

    public function reRunSimulation(Simulation $simulation): void
    {
        $this->checkIfItHasMatches($simulation);

        // Remove existing team points
        foreach ($simulation->teamPoints as $teamPoint) {
            $this->entityManager->remove($teamPoint);
        }

        $this->run($simulation);
    }

    private function run(Simulation $simulation): void
    {
        // Initialize team points tracking
        $teamPoints = [];

        // Process matches in order (already sorted by the "stepNumber" property in the Simulation entity)
        foreach ($simulation->matches as $match) {
            $homeTeam = $match->homeTeam;
            $awayTeam = $match->awayTeam;

            // Get current points for both teams (or use initial points if not set yet)
            $homeTeamCurrentPoints = $teamPoints[$homeTeam->id] ?? $homeTeam->points;
            $awayTeamCurrentPoints = $teamPoints[$awayTeam->id] ?? $awayTeam->points;

            // Calculate points exchange
            $pointsExchange = $this->pointsCalculator->calculateExchangedPoints(
                $homeTeamCurrentPoints,
                $awayTeamCurrentPoints,
                $match->homeScore,
                $match->awayScore,
                $match->isNeutralGround,
                $match->isWorldCup
            );

            // Update points for both teams
            $newHomeTeamPoints = $homeTeamCurrentPoints + $pointsExchange;
            $newAwayTeamPoints = $awayTeamCurrentPoints - $pointsExchange;

            // Store updated points for next iterations
            $teamPoints[$homeTeam->id] = $newHomeTeamPoints;
            $teamPoints[$awayTeam->id] = $newAwayTeamPoints;

            // Create TeamPoints entities for both teams
            $homeTeamPoint = new TeamPoints();
            $homeTeamPoint->setTeam($homeTeam)
                ->setSimulation($simulation)
                ->setStepNumber($match->stepNumber)
                ->setPoints($newHomeTeamPoints);

            $awayTeamPoint = new TeamPoints();
            $awayTeamPoint->setTeam($awayTeam)
                ->setSimulation($simulation)
                ->setStepNumber($match->stepNumber)
                ->setPoints($newAwayTeamPoints);

            // Add team points to simulation
            $simulation->addTeamPoint($homeTeamPoint);
            $simulation->addTeamPoint($awayTeamPoint);

            // Persist the new team points
            $this->entityManager->persist($homeTeamPoint);
            $this->entityManager->persist($awayTeamPoint);
        }

        $this->entityManager->flush();
    }

    private function checkIfItHasMatches(Simulation $simulation): void
    {
        // Check if the simulation has rugby matches
        if ($simulation->matches->isEmpty()) {
            throw new InvalidSimulationException('Simulation has no rugby matches');
        }
    }
}
