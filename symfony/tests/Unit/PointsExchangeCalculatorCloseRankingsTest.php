<?php

namespace App\Tests\Unit;

use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorCloseRankingsTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    /**
     * @dataProvider matchScenarioProvider
     */
    public function test_between_teams_with_close_rankings(
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup,
        float $expectedPointsExchange
    ): void {
        // Calculate points exchange
        $pointsExchanged = $this->calculator->calculateExchangedPoints(
            92.777351682669, // South Africa
            90.362919014871, // New Zealand
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
                0.4586, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.5414432667798 = 0.4585567332202
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.4585567332202 * 1.0 = 0.4586
            ],
            'SA loses at home' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -1.5414, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.5414432667798) = -1.5414432667798
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.5414432667798 * 1.0 = -1.5414
            ],
            'Draw at home' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -0.5414, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Draw, so P = -D/10 = -0.5414432667798
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.5414432667798 * 1.0 = -0.5414
            ],
            'SA wins at neutral ground' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                0.7586, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.2414432667798 = 0.7585567332202
                // 6. No large victory (27-20 = 7 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = 0.7585567332202 * 1.0 = 0.7586
            ],
            'SA loses at neutral ground' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.2414, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.2414432667798) = -1.2414432667798
                // 6. No large victory (25-15 = 10 points difference < 15), no World Cup, so weight = 1.0
                // 7. Final points exchange = -1.2414432667798 * 1.0 = -1.2414
            ],
            'Draw at neutral ground' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -0.2414, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Draw, so P = -D/10 = -0.2414432667798
                // 6. No World Cup, so weight = 1.0
                // 7. Final points exchange = -0.2414432667798 * 1.0 = -0.2414
            ],
            'SA wins big at home' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                0.6878, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.5414432667798 = 0.4585567332202
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.4585567332202 * 1.5 = 0.6878
            ],
            'SA loses big at home' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                false, // isWorldCup
                -2.3122, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.5414432667798) = -1.5414432667798
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.5414432667798 * 1.5 = -2.3122
            ],
            'SA wins at home in World Cup' => [
                27, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                0.9171, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.5414432667798 = 0.4585567332202
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.4585567332202 * 2.0 = 0.9171
            ],
            'SA loses at home in World Cup' => [
                15, // homeScore
                25, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -3.0829, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.5414432667798) = -1.5414432667798
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.5414432667798 * 2.0 = -3.0829
            ],
            'SA wins big at home in World Cup' => [
                35, // homeScore
                10, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                1.3757, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.5414432667798 = 0.4585567332202
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.4585567332202 * 3.0 = 1.3757
            ],
            'SA loses big at home in World Cup' => [
                10, // homeScore
                35, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -4.6243, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.5414432667798) = -1.5414432667798
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.5414432667798 * 3.0 = -4.6243
            ],
            'Draw at home in World Cup' => [
                20, // homeScore
                20, // awayScore
                false, // isNeutralGround
                true, // isWorldCup
                -1.0829, // expectedPointsExchange
                // Calculation:
                // 1. Home advantage applied: 92.777351682669 + 3 = 95.777351682669
                // 2. Rating difference: 95.777351682669 - 90.362919014871 = 5.414432667798
                // 3. Rating difference capped at 10: 5.414432667798 (unchanged)
                // 4. Rating factor: 5.414432667798 / 10 = 0.5414432667798
                // 5. Draw, so P = -D/10 = -0.5414432667798
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.5414432667798 * 2.0 = -1.0829
            ],
            'Draw at neutral ground in World Cup' => [
                20, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -0.4829, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Draw, so P = -D/10 = -0.2414432667798
                // 6. World Cup match, so weight = 2.0
                // 7. Final points exchange = -0.2414432667798 * 2.0 = -0.4829
            ],
            'SA wins at neutral ground in World Cup' => [
                27, // homeScore
                20, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                1.5171, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.2414432667798 = 0.7585567332202
                // 6. No large victory (27-20 = 7 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = 0.7585567332202 * 2.0 = 1.5171
            ],
            'SA loses at neutral ground in World Cup' => [
                15, // homeScore
                25, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -2.4829, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.2414432667798) = -1.2414432667798
                // 6. No large victory (25-15 = 10 points difference < 15), but World Cup match, so weight = 2.0
                // 7. Final points exchange = -1.2414432667798 * 2.0 = -2.4829
            ],
            'SA wins big at neutral ground in World Cup' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                2.2757, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.2414432667798 = 0.7585567332202
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = 0.7585567332202 * 3.0 = 2.2757
            ],
            'SA loses big at neutral ground in World Cup' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                true, // isWorldCup
                -3.7243, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.2414432667798) = -1.2414432667798
                // 6. Large victory (35-10 = 25 points difference > 15) and World Cup match, so weight = 1.5 * 2.0 = 3.0
                // 7. Final points exchange = -1.2414432667798 * 3.0 = -3.7243
            ],
            'SA wins big at neutral ground' => [
                35, // homeScore
                10, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                1.1378, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team wins, so P = 1 - D/10 = 1 - 0.2414432667798 = 0.7585567332202
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = 0.7585567332202 * 1.5 = 1.1378
            ],
            'SA loses big at neutral ground' => [
                10, // homeScore
                35, // awayScore
                true, // isNeutralGround
                false, // isWorldCup
                -1.8622, // expectedPointsExchange
                // Calculation:
                // 1. Neutral ground, so no home advantage: 92.777351682669 (unchanged)
                // 2. Rating difference: 92.777351682669 - 90.362919014871 = 2.414432667798
                // 3. Rating difference capped at 10: 2.414432667798 (unchanged)
                // 4. Rating factor: 2.414432667798 / 10 = 0.2414432667798
                // 5. Home team loses, so P = -(1 + D/10) = -(1 + 0.2414432667798) = -1.2414432667798
                // 6. Large victory (35-10 = 25 points difference > 15), so weight = 1.5
                // 7. Final points exchange = -1.2414432667798 * 1.5 = -1.8622
            ]
        ];
    }
}
