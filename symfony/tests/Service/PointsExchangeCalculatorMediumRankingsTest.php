<?php

namespace App\Tests\Service;

use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorMediumRankingsTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    /**
     * @dataProvider matchScenarioProvider
     */
    public function test_between_england_and_australia(
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup,
        float $expectedPointsExchange
    ): void {
        // Calculate points exchange
        $pointsExchanged = $this->calculator->calculateExchangedPoints(
            85.14159265359, // England
            80.27182818285, // Australia
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
     * Data provider for match scenarios between England and Australia
     * 
     * @return array<string, array{0: int, 1: int, 2: bool, 3: bool, 4: float}>
     */
    public function matchScenarioProvider(): array
    {
        return [
            'England wins at home' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.2130, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.7869764470740 = 0.2130235529260
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.2130235529260 * 1.0 = 0.2130 (rounded)
            ],
            'England loses at home' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -1.7870, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.7869764470740) = -1.7869764470740
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.7869764470740 * 1.0 = -1.7870 (rounded)
            ],
            'Draw at home' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -0.7870, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Draw, so P = -D/10 = -0.7869764470740
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.7869764470740 * 1.0 = -0.7870 (rounded)
            ],
            'England wins at neutral ground' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.5130, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.4869764470740 = 0.5130235529260
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.5130235529260 * 1.0 = 0.5130 (rounded)
            ],
            'England loses at neutral ground' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.4870, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.4869764470740) = -1.4869764470740
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.4869764470740 * 1.0 = -1.4870 (rounded)
            ],
            'Draw at neutral ground' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -0.4870, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Draw, so P = -D/10 = -0.4869764470740
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.4869764470740 * 1.0 = -0.4870 (rounded)
            ],
            'England wins big at home' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.3195, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.7869764470740 = 0.2130235529260
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.2130235529260 * 1.5 = 0.3195 (rounded)
            ],
            'England loses big at home' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -2.6805, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.7869764470740) = -1.7869764470740
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.7869764470740 * 1.5 = -2.6805 (rounded)
            ],
            'England wins at home in World Cup' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.4260, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.7869764470740 = 0.2130235529260
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.2130235529260 * 2.0 = 0.4260 (rounded)
            ],
            'England loses at home in World Cup' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -3.5740, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.7869764470740) = -1.7869764470740
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.7869764470740 * 2.0 = -3.5740 (rounded)
            ],
            'England wins big at home in World Cup' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.6391, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.7869764470740 = 0.2130235529260
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.2130235529260 * 3.0 = 0.6391 (rounded)
            ],
            'England loses big at home in World Cup' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -5.3609, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.7869764470740) = -1.7869764470740
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.7869764470740 * 3.0 = -5.3609 (rounded)
            ],
            'Draw at home in World Cup' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -1.5740, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 85.14159265359 + 3 = 88.14159265359
                // 2. Rating difference: 88.14159265359 - 80.27182818285 = 7.86976447074
                // 3. Rating difference capped at 10: 7.86976447074 (unchanged)
                // 4. Rating factor: 7.86976447074 / 10 = 0.7869764470740
                // 5. Draw, so P = -D/10 = -0.7869764470740
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.7869764470740 * 2.0 = -1.5740 (rounded)
            ],
            'Draw at neutral ground in World Cup' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -0.9740, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Draw, so P = -D/10 = -0.4869764470740
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.4869764470740 * 2.0 = -0.9740 (rounded)
            ],
            'England wins at neutral ground in World Cup' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                1.0260, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.4869764470740 = 0.5130235529260
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.5130235529260 * 2.0 = 1.0260 (rounded)
            ],
            'England loses at neutral ground in World Cup' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -2.9740, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.4869764470740) = -1.4869764470740
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.4869764470740 * 2.0 = -2.9740 (rounded)
            ],
            'England wins big at neutral ground in World Cup' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                1.5391, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.4869764470740 = 0.5130235529260
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.5130235529260 * 3.0 = 1.5391 (rounded)
            ],
            'England loses big at neutral ground in World Cup' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -4.4609, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.4869764470740) = -1.4869764470740
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.4869764470740 * 3.0 = -4.4609 (rounded)
            ],
            'England wins big at neutral ground' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.7695, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.4869764470740 = 0.5130235529260
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.5130235529260 * 1.5 = 0.7695 (rounded)
            ],
            'England loses big at neutral ground' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -2.2305, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 85.14159265359 (unchanged)
                // 2. Rating difference: 85.14159265359 - 80.27182818285 = 4.86976447074
                // 3. Rating difference capped at 10: 4.86976447074 (unchanged)
                // 4. Rating factor: 4.86976447074 / 10 = 0.4869764470740
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.4869764470740) = -1.4869764470740
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.4869764470740 * 1.5 = -2.2305 (rounded)
            ]
        ];
    }
}
