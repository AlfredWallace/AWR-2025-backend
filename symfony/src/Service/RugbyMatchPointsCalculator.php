<?php

namespace App\Service;

use App\Entity\RugbyMatch;
use App\Repository\TeamPointsRepository;

readonly class RugbyMatchPointsCalculator
{
    public function __construct(
        private TeamPointsRepository $teamPointsRepository,
        private PointsExchangeCalculator $pointsExchangeCalculator
    ) {
    }
    
    /**
     * Calculate the points exchanged between two teams for a rugby match.
     * Based on World Rugby's official ranking calculation system.
     * This function extracts data from the RugbyMatch object, delegates to the algorithm,
     * and applies the points exchange to the correct teams.
     * 
     * @param RugbyMatch $match The match object containing teams and scores
     * 
     * @return array{homePoints: float, awayPoints: float} Points gained/lost by each team
     */
    public function calculatePoints(RugbyMatch $match): array
    {
        $simulation = $match->simulation;
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;
        $matchOrder = $match->order;
        
        // Find the most recent team points entries before this match
        $homeTeamSimulationLastPoints = $this->teamPointsRepository->findMostRecentTeamPointsBeforeOrder(
            $simulation,
            $homeTeam,
            $matchOrder
        );
        
        $awayTeamSimulationLastPoints = $this->teamPointsRepository->findMostRecentTeamPointsBeforeOrder(
            $simulation,
            $awayTeam,
            $matchOrder
        );
        
        // If no previous points found in simulation, use the team's base points
        $homeTeamRanking = $homeTeamSimulationLastPoints ? $homeTeamSimulationLastPoints->points : $homeTeam->points;
        $awayTeamRanking = $awayTeamSimulationLastPoints ? $awayTeamSimulationLastPoints->points : $awayTeam->points;
        
        // Get the points to be exchanged from the algorithm
        $pointsToExchange = $this->pointsExchangeCalculator->calculateExchangedPoints(
            $homeTeamRanking,
            $awayTeamRanking,
            $match->homeScore,
            $match->awayScore,
            $match->isNeutralGround,
            $match->isWorldCup
        );
        
        // Determine who gets the points based on the match result using match expression
        [$homePointsChange, $awayPointsChange] = match (true) {
            // Home team won
            $match->homeScore > $match->awayScore => [
                $pointsToExchange, 
                -$pointsToExchange
            ],
            
            // Away team won
            $match->homeScore < $match->awayScore => [
                -$pointsToExchange, 
                $pointsToExchange
            ],
            
            // Draw - points are still exchanged based on the pre-match rankings
            $homeTeamRanking > $awayTeamRanking => [
                -$pointsToExchange,  // Home team was higher-ranked, they lose points
                $pointsToExchange
            ],
            $homeTeamRanking < $awayTeamRanking => [
                $pointsToExchange,   // Away team was higher-ranked, they lose points
                -$pointsToExchange
            ],
            
            // Teams were equally ranked, no points exchanged
            default => [0.0, 0.0]
        };
        
        return [
            'homePoints' => $homePointsChange,
            'awayPoints' => $awayPointsChange
        ];
    }
}