<?php

namespace App\Service;

use App\Entity\RugbyMatch;
use App\Repository\TeamPointsRepository;

readonly class RugbyMatchPointsCalculator
{
    private const float RATING_CAP = 10.0;
    private const float HOME_ADVANTAGE = 3.0;
    private const float WEIGHT_WORLD_CUP = 2.0;
    private const float WEIGHT_LARGE_VICTORY = 1.5;
    private const int LARGE_VICTORY_THRESHOLD = 15;
    
    public function __construct(
        private TeamPointsRepository $teamPointsRepository
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
        $pointsToExchange = $this->calculatePointsExchange(
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
    
    /**
     * Pure algorithmic function to calculate points exchange based on numeric parameters.
     * Returns a single value representing the points to be exchanged between teams.
     * 
     * @param float $homeTeamRanking     Home team's current ranking points
     * @param float $awayTeamRanking     Away team's current ranking points
     * @param int $homeScore             Home team's score in the match
     * @param int $awayScore             Away team's score in the match
     * @param bool $isNeutralGround      Whether the match is played on neutral ground
     * @param bool $isWorldCup           Whether the match is part of the World Cup
     * 
     * @return float Points to be exchanged between teams
     */
    private function calculatePointsExchange(
        float $homeTeamRanking,
        float $awayTeamRanking,
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup
    ): float {
        // Step 1: Apply home advantage to home team's rating (only if not on neutral ground)
        $homeTeamEffectiveRating = $isNeutralGround 
            ? $homeTeamRanking
            : $homeTeamRanking + self::HOME_ADVANTAGE;
        
        // Step 2: Calculate rating difference with home advantage applied
        $ratingDifference = $homeTeamEffectiveRating - $awayTeamRanking;
        
        // Step 3: Apply rating cap (max 10 points difference considered)
        $cappedRatingDifference = max(-self::RATING_CAP, min(self::RATING_CAP, $ratingDifference));
        
        // Step 4: Calculate expected outcome for home team
        // Formula: 1 / (1 + 10^(-ratingDiff/10))
        $homeExpectedWin = 1 / (1 + pow(10, -$cappedRatingDifference / 10));
        
        // Step 5: Determine actual match outcome (1 for win, 0.5 for draw, 0 for loss)
        if ($homeScore > $awayScore) {
            $homeActualOutcome = 1;
        } elseif ($homeScore < $awayScore) {
            $homeActualOutcome = 0;
        } else {
            $homeActualOutcome = 0.5;
        }
        
        // Step 6: Calculate base points exchange
        $basePointsExchange = abs($homeActualOutcome - $homeExpectedWin);
        
        // Step 7: Calculate weight based on match circumstances
        $weight = 1.0;
        
        // Apply large victory multiplier if appropriate
        if (abs($homeScore - $awayScore) > self::LARGE_VICTORY_THRESHOLD) {
            $weight *= self::WEIGHT_LARGE_VICTORY;
        }
        
        // Apply World Cup multiplier if appropriate
        if ($isWorldCup) {
            $weight *= self::WEIGHT_WORLD_CUP;
        }
        
        // Step 8: Calculate final points to exchange
        return $weight * $basePointsExchange;
    }
    
}