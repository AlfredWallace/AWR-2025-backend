<?php

namespace App\Tests\Service;

use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    public function testMatchBetweenSouthAfricaAndNewZealand(): void
    {
        // South Africa (92.77 points) vs New Zealand (90.36 points)
        $homeTeamRanking = 92.77; // South Africa
        $awayTeamRanking = 90.36; // New Zealand
        
        // Scenario: South Africa (home team) wins with a score of 27-20
        $homeScore = 27;
        $awayScore = 20;

        // Calculate points exchange
        $pointsExchanged = $this->calculator->calculateExchangedPoints(
            $homeTeamRanking,
            $awayTeamRanking,
            $homeScore,
            $awayScore,
            false,
            false
        );
        
        // Calculate the expected points exchange manually
        // Step 1: Home advantage applied: 92.77 + 3 = 95.77
        // Step 2: Rating difference: 95.77 - 90.36 = 5.41
        // Step 3: Rating difference capped at 10: 5.41 (unchanged)
        // Step 4: Rating factor: 5.41 / 10 = 0.541
        // Step 5: Home team wins, so P = 1 - D/10 = 1 - 0.541 = 0.459
        // Step 6: No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
        // Step 7: Final points exchange = 0.459 * 1.0 = 0.459
        $expectedPointsExchange = 0.459;
        
        // Use a single assertion with a small delta for floating-point precision
        $this->assertEqualsWithDelta(
            $expectedPointsExchange,
            $pointsExchanged,
            0.0001,
            'Points exchanged should match the calculated value' // Delta value for floating-point comparison
        );
    }
}
