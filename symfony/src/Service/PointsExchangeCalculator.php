<?php

namespace App\Service;

/**
 * Implementation of the World Rugby ranking points exchange system.
 * Based on the official World Rugby ranking calculation rules.
 * @see https://www.world.rugby/rankings/explanation
 */
readonly class PointsExchangeCalculator
{
    /**
     * Maximum difference in ratings to be considered for points exchange calculation.
     * The World Rugby system caps the rating difference at 10 points.
     */
    private const float RATING_CAP = 10.0;
    
    /**
     * Points advantage given to the home team.
     * According to World Rugby rules, home teams receive a 3-point advantage.
     */
    private const float HOME_ADVANTAGE = 3.0;
    
    /**
     * Multiplier for World Cup matches.
     * World Cup matches have double the impact on rankings.
     */
    private const float WEIGHT_WORLD_CUP = 2.0;
    
    /**
     * Multiplier for matches with large victory margins.
     * Victories by more than 15 points are considered significant.
     */
    private const float WEIGHT_LARGE_VICTORY = 1.5;
    
    /**
     * Point difference threshold for considering a victory as "large".
     * World Rugby considers a victory margin greater than 15 points as significant.
     */
    private const int LARGE_VICTORY_THRESHOLD = 15;
    
    /**
     * Calculates the points to be exchanged between teams after a match.
     * Follows the official World Rugby "Points Exchange" system.
     * 
     * @param float $homeTeamRanking     Home team's current ranking points
     * @param float $awayTeamRanking     Away team's current ranking points
     * @param int $homeScore             Home team's score in the match
     * @param int $awayScore             Away team's score in the match
     * @param bool $isNeutralGround      Whether the match is played on neutral ground
     * @param bool $isWorldCup           Whether the match is part of the World Cup
     * 
     * @return float Points to be exchanged between teams (positive means home team gains points)
     */
    public function calculateExchangedPoints(
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
        
        // Step 4: Calculate points exchange based on World Rugby's direct formulas:
        // P = 1 - D/10 if the team wins
        // P = -(1 + D/10) if the team loses
        // P = -D/10 if there's a draw
        // Where D is the rating difference between teams
        
        $ratingFactor = $cappedRatingDifference / 10;
        
        if ($homeScore > $awayScore) {
            // Home team wins: P = 1 - D/10
            $basePointsExchange = 1 - $ratingFactor;
        } elseif ($homeScore < $awayScore) {
            // Home team loses: P = -(1 + D/10)
            $basePointsExchange = -(1 + $ratingFactor);
        } else {
            // Draw: P = -D/10
            $basePointsExchange = -$ratingFactor;
        }
        
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
