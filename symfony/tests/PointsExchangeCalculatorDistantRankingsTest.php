<?php

namespace App\Tests;

use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorDistantRankingsTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    /**
     * @dataProvider matchScenarioProvider
     */
    public function test_between_ireland_and_italy(
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup,
        float $expectedPointsExchange
    ): void {
        // Calculate points exchange
        $pointsExchanged = $this->calculator->calculateExchangedPoints(
            89.82737619726, // Ireland
            77.771601211987, // Italy
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
     * Data provider for match scenarios between Ireland and Italy
     * 
     * @return array<string, array{0: int, 1: int, 2: bool, 3: bool, 4: float}>
     */
    public function matchScenarioProvider(): array
    {
        return [
            'Ireland wins at home' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10 (capped)
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.0 * 1.0 = 0.0
            ],
            'Ireland loses at home' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -2.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -2.0 * 1.0 = -2.0
            ],
            'Draw at home' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -1.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Draw, so P = -D/10 = -1.0
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -1.0 * 1.0 = -1.0
            ],
            'Ireland wins at neutral ground' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.0 * 1.0 = 0.0
            ],
            'Ireland loses at neutral ground' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -2.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -2.0 * 1.0 = -2.0
            ],
            'Draw at neutral ground' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Draw, so P = -D/10 = -1.0
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -1.0 * 1.0 = -1.0
            ],
            'Ireland wins big at home' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.0 * 1.5 = 0.0
            ],
            'Ireland loses big at home' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -3.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -2.0 * 1.5 = -3.0
            ],
            'Ireland wins at home in World Cup' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.0 * 2.0 = 0.0
            ],
            'Ireland loses at home in World Cup' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -4.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -2.0 * 2.0 = -4.0
            ],
            'Ireland wins big at home in World Cup' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.0 * 3.0 = 0.0
            ],
            'Ireland loses big at home in World Cup' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -6.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -2.0 * 3.0 = -6.0
            ],
            'Draw at home in World Cup' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -2.0, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 89.82737619726 + 3 = 92.82737619726
                // 2. Rating difference: 92.82737619726 - 77.771601211987 = 15.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Draw, so P = -D/10 = -1.0
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.0 * 2.0 = -2.0
            ],
            'Draw at neutral ground in World Cup' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -2.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Draw, so P = -D/10 = -1.0
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.0 * 2.0 = -2.0
            ],
            'Ireland wins at neutral ground in World Cup' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.0 * 2.0 = 0.0
            ],
            'Ireland loses at neutral ground in World Cup' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -4.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -2.0 * 2.0 = -4.0
            ],
            'Ireland wins big at neutral ground in World Cup' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.0 * 3.0 = 0.0
            ],
            'Ireland loses big at neutral ground in World Cup' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -6.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -2.0 * 3.0 = -6.0
            ],
            'Ireland wins big at neutral ground' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team wins, so P = 1 - D/10 = 1 - 1.0 = 0.0
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.0 * 1.5 = 0.0
            ],
            'Ireland loses big at neutral ground' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -3.0, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 89.82737619726 (unchanged)
                // 2. Rating difference: 89.82737619726 - 77.771601211987 = 12.055774985273
                // 3. Rating difference capped at 10: 10
                // 4. Rating factor: 10 / 10 = 1.0
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 1.0) = -2.0
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -2.0 * 1.5 = -3.0
            ]
        ];
    }
}
