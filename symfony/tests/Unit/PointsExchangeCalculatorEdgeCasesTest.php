<?php

namespace App\Tests\Unit;

use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorEdgeCasesTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    /**
     * @dataProvider zeroRankingDataProvider
     */
    public function test_zero_ranking_teams(
        float $homeTeamRanking,
        float $awayTeamRanking,
        int $homeScore,
        int $awayScore,
        bool $isNeutralGround,
        bool $isWorldCup,
        float $expectedPoints
    ): void {
        $exchangedPoints = $this->calculator->calculateExchangedPoints(
            $homeTeamRanking,
            $awayTeamRanking,
            $homeScore,
            $awayScore,
            $isNeutralGround,
            $isWorldCup
        );

        // Use a single assertion with a small delta for floating-point precision
        $this->assertEqualsWithDelta(
            $expectedPoints,
            $exchangedPoints,
            0.0001,
            'Points exchanged should match the calculated value' // Delta value for floating-point comparison
        );
    }

    /**
     * @return array<string, array{0: float, 1: float, 2: int, 3: int, 4: bool, 5: bool, 6: float}>
     */
    public function zeroRankingDataProvider(): array
    {
        return [
            'extremely low rankings for both teams' => [
                0.1, // Home team ranking
                0.1, // Away team ranking
                20,  // Home score
                10,  // Away score
                false, // Not neutral ground
                false, // Not world cup
                0.7    // Expected points
                // Calculation:
                // 1. Home advantage: 0.1 + 3 = 3.1
                // 2. Rating difference: 3.1 - 0.1 = 3.0
                // 3. Capped difference: 3.0 (within cap)
                // 4. Rating factor: 3.0 / 10 = 0.3
                // 5. Home team won, so: 1 - 0.3 = 0.7
                // 6. No large victory weight (10 point difference < 15)
                // 7. No World Cup multiplier
                // 8. Final: 1 * 0.7 = 0.7
            ],
            'extremely high rankings for both teams' => [
                100.0, // Home team ranking
                100.0, // Away team ranking
                20,    // Home score
                10,    // Away score
                false, // Not neutral ground
                false, // Not world cup
                0.7    // Expected points
                // Calculation:
                // 1. Home advantage: 100 + 3 = 103
                // 2. Rating difference: 103 - 100 = 3.0
                // 3. Capped difference: 3.0 (within cap)
                // 4. Rating factor: 3.0 / 10 = 0.3
                // 5. Home team won, so: 1 - 0.3 = 0.7
                // 6. No large victory weight (10 point difference < 15)
                // 7. No World Cup multiplier
                // 8. Final: 1 * 0.7 = 0.7
            ],
            'exactly 10 point ranking gap with home team stronger' => [
                90.0, // Home team ranking
                80.0, // Away team ranking
                20,   // Home score
                10,   // Away score
                false, // Not neutral ground
                false, // Not world cup
                0.0   // Expected points
                // Calculation:
                // 1. Home advantage: 90 + 3 = 93
                // 2. Rating difference: 93 - 80 = 13.0
                // 3. Capped difference: 10.0 (capped at maximum 10)
                // 4. Rating factor: 10.0 / 10 = 1.0
                // 5. Home team won, so: 1 - 1.0 = 0.0
                // 6. No large victory weight (10 point difference < 15)
                // 7. No World Cup multiplier
                // 8. Final: 1 * 0.0 = 0.0
            ],
            'exactly 10 point ranking gap with away team stronger' => [
                80.0, // Home team ranking
                90.0, // Away team ranking
                20,   // Home score
                10,   // Away score
                false, // Not neutral ground
                false, // Not world cup
                1.7   // Expected points
                // Calculation:
                // 1. Home advantage: 80 + 3 = 83
                // 2. Rating difference: 83 - 90 = -7.0
                // 3. Capped difference: -7.0 (within cap)
                // 4. Rating factor: -7.0 / 10 = -0.7
                // 5. Home team won, so: 1 - (-0.7) = 1.7
                // 6. No large victory weight (10 point difference < 15)
                // 7. No World Cup multiplier
                // 8. Final: 1 * 1.7 = 1.7
            ],
            'both teams with zero ranking' => [
                0.0, // Home team ranking
                0.0, // Away team ranking
                20,  // Home score
                10,  // Away score
                false, // Not neutral ground
                false, // Not world cup
                0.7   // Expected points
                // Calculation:
                // 1. Home advantage: 0 + 3 = 3
                // 2. Rating difference: 3 - 0 = 3.0
                // 3. Capped difference: 3.0 (within cap)
                // 4. Rating factor: 3.0 / 10 = 0.3
                // 5. Home team won, so: 1 - 0.3 = 0.7
                // 6. No large victory weight (10 point difference < 15)
                // 7. No World Cup multiplier
                // 8. Final: 1 * 0.7 = 0.7
            ],
        ];
    }
}
