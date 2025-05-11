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
        // With the direct formula implementation, the sign of the result already indicates
        // which team gains or loses points:
        // - Positive value: Home team gains points, away team loses points
        // - Negative value: Home team loses points, away team gains points
        $pointsExchange = $this->pointsExchangeCalculator->calculateExchangedPoints(
            $homeTeamRanking,
            $awayTeamRanking,
            $match->homeScore,
            $match->awayScore,
            $match->isNeutralGround,
            $match->isWorldCup
        );
        
        // Since the sign already indicates which team gains or loses points,
        // we just need to assign the values with opposite signs
        $homePointsChange = $pointsExchange;
        $awayPointsChange = -$pointsExchange;
        
        return [
            'homePoints' => $homePointsChange,
            'awayPoints' => $awayPointsChange
        ];
    }
}