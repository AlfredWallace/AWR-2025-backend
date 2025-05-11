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

    /**
     * @dataProvider matchScenarioProvider
     */
    public function testMatchBetweenSouthAfricaAndNewZealand(
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup,
        float $expectedPointsExchange
    ): void {
        // Calculate points exchange
        $pointsExchanged = $this->calculator->calculateExchangedPoints(
            92.77, // South Africa
            90.36, // New Zealand
            $homeScore,
            $awayScore,
            $isNeutralGround,
            $isWorldCup
        );
        
        // Use a single assertion with a small delta for floating-point precision
        $this->assertEqualsWithDelta(
            $expectedPointsExchange,
            $pointsExchanged,
            0.0001,
            'Points exchanged should match the calculated value' // Delta value for floating-point comparison
        );
    }
    
    /**
     * Data provider for match scenarios between South Africa and New Zealand
     * 
     * @return array<string, array{0: int, 1: int, 2: bool, 3: bool, 4: float}>
     */
    public function matchScenarioProvider(): array
    {
        return [
            'SA wins at home' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.459, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.541 = 0.459
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.459 * 1.0 = 0.459
            ],
            'SA loses at home' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -1.541, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // A3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.541) = -1.541
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.541 * 1.0 = -1.541
            ],
            'Draw at home' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -0.541, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Draw, so P = -D/10 = -0.541
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.541 * 1.0 = -0.541
            ],
            'SA wins at neutral ground' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.759, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.241 = 0.759
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.759 * 1.0 = 0.759
            ],
            'SA loses at neutral ground' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.241, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.241) = -1.241
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.241 * 1.0 = -1.241
            ],
            'Draw at neutral ground' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -0.241, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Draw, so P = -D/10 = -0.241
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.241 * 1.0 = -0.241
            ],
            'SA wins big at home' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.6885, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.541 = 0.459
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.459 * 1.5 = 0.6885
            ],
            'SA loses big at home' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -2.3115, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.541) = -1.541
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.541 * 1.5 = -2.3115
            ],
            'SA wins at home in World Cup' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.918, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.541 = 0.459
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.459 * 2.0 = 0.918
            ],
            'SA loses at home in World Cup' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -3.082, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.541) = -1.541
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.541 * 2.0 = -3.082
            ],
            'SA wins big at home in World Cup' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                1.377, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.541 = 0.459
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.459 * 3.0 = 1.377
            ],
            'SA loses big at home in World Cup' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -4.623, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.541) = -1.541
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.541 * 3.0 = -4.623
            ],
            'Draw at home in World Cup' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -1.082, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.77 + 3 = 95.77
                // 2. Rating difference: 95.77 - 90.36 = 5.41
                // 3. Rating difference capped at 10: 5.41 (unchanged)
                // 4. Rating factor: 5.41 / 10 = 0.541
                // 5. Draw, so P = -D/10 = -0.541
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.541 * 2.0 = -1.082
            ],
            'Draw at neutral ground in World Cup' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -0.482, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Draw, so P = -D/10 = -0.241
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.241 * 2.0 = -0.482
            ],
            'SA wins at neutral ground in World Cup' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                1.518, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.241 = 0.759
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.759 * 2.0 = 1.518
            ],
            'SA loses at neutral ground in World Cup' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -2.482, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.241) = -1.241
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.241 * 2.0 = -2.482
            ],
            'SA wins big at neutral ground in World Cup' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                2.277, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.241 = 0.759
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.759 * 3.0 = 2.277
            ],
            'SA loses big at neutral ground in World Cup' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -3.723, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.241) = -1.241
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.241 * 3.0 = -3.723
            ],
            'SA wins big at neutral ground' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                1.1385, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.241 = 0.759
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.759 * 1.5 = 1.1385
            ],
            'SA loses big at neutral ground' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.8615, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.77 (unchanged)
                // 2. Rating difference: 92.77 - 90.36 = 2.41
                // 3. Rating difference capped at 10: 2.41 (unchanged)
                // 4. Rating factor: 2.41 / 10 = 0.241
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.241) = -1.241
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.241 * 1.5 = -1.8615
            ]
        ];
    }
}
