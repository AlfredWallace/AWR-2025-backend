<?php

namespace App\Service;

use App\Entity\RugbyMatch;

class PointsExchangeCalculator
{
    private const float RATING_CAP = 10.0;
    private const float HOME_ADVANTAGE = 3.0;
    private const float WEIGHT_WORLD_CUP = 2.0;
    private const float WEIGHT_LARGE_VICTORY = 1.5;
    private const int LARGE_VICTORY_THRESHOLD = 15;
    
    /**
     * Calculate the points exchanged between two teams for a rugby match.
     * Based on World Rugby's official ranking calculation system.
     * 
     * @param RugbyMatch $match The match object containing teams and scores
     * @param float $homeTeamPoints Rating points of the home team before the match
     * @param float $awayTeamPoints Rating points of the away team before the match
     * 
     * @return array{homePoints: float, awayPoints: float} Points gained/lost by each team
     */
    public function calculateExchangedPoints(
        RugbyMatch $match, 
        float $homeTeamPoints, 
        float $awayTeamPoints
    ): array {
        // Step 1: Apply home advantage to home team's rating
        $homeTeamEffectiveRating = $homeTeamPoints + self::HOME_ADVANTAGE;
        
        // Step 2: Calculate rating difference with home advantage applied
        $ratingDifference = $homeTeamEffectiveRating - $awayTeamPoints;
        
        // Step 3: Apply rating cap (max 10 points difference considered)
        $cappedRatingDifference = max(-self::RATING_CAP, min(self::RATING_CAP, $ratingDifference));
        
        // Step 4: Calculate expected outcome for home team
        // Formula: 1 / (1 + 10^(-ratingDiff/10))
        $homeExpectedWin = 1 / (1 + pow(10, -$cappedRatingDifference / 10));
        $awayExpectedWin = 1 - $homeExpectedWin;
        
        // Step 5: Determine actual match outcome (1 for win, 0.5 for draw, 0 for loss)
        if ($match->homeScore > $match->awayScore) {
            $homeActualOutcome = 1;
            $awayActualOutcome = 0;
        } elseif ($match->homeScore < $match->awayScore) {
            $homeActualOutcome = 0;
            $awayActualOutcome = 1;
        } else {
            $homeActualOutcome = 0.5;
            $awayActualOutcome = 0.5;
        }
        
        // Step 6: Calculate base points exchange
        $baseHomePointsChange = $homeActualOutcome - $homeExpectedWin;
        $baseAwayPointsChange = $awayActualOutcome - $awayExpectedWin;
        
        // Step 7: Apply weighting rules
        $weight = $this->getMatchWeight($match);
        
        // Step 8: Calculate final points exchange
        // Points gained = weight * (actual outcome - expected outcome)
        $homePointsChange = $weight * $baseHomePointsChange;
        $awayPointsChange = $weight * $baseAwayPointsChange;
        
        // Round to 2 decimal places
        return [
            'homePoints' => round($homePointsChange, 2),
            'awayPoints' => round($awayPointsChange, 2)
        ];
    }
    
    /**
     * Get the weight multiplier based on the match type and result margin.
     * 
     * Rules:
     * 1. If one side has won by more than 15 points, multiply by 1.5
     * 2. If the match was part of the World Cup Finals, double the Rating Change
     */
    private function getMatchWeight(RugbyMatch $match): float
    {
        $weight = 1.0;
        
        // Apply large victory multiplier if appropriate
        if (abs($match->homeScore - $match->awayScore) > self::LARGE_VICTORY_THRESHOLD) {
            $weight *= self::WEIGHT_LARGE_VICTORY;
        }
        
        // Apply World Cup multiplier if appropriate
        if ($match->isWorldCup) {
            $weight *= self::WEIGHT_WORLD_CUP;
        }
        
        return $weight;
    }
}