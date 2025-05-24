<?php

namespace App\Tests\Unit;

use App\Exception\InvalidCalculatorDataException;
use App\Service\PointsExchangeCalculator;
use PHPUnit\Framework\TestCase;

class PointsExchangeCalculatorInvalidDataTest extends TestCase
{
    private PointsExchangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PointsExchangeCalculator();
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function test_negative_values(
        float $homeTeamRanking,
        float $awayTeamRanking,
        int $homeScore,
        int $awayScore
    ): void {
        $this->expectException(InvalidCalculatorDataException::class);

        $this->calculator->calculateExchangedPoints(
            $homeTeamRanking,
            $awayTeamRanking,
            $homeScore,
            $awayScore,
            false,
            false
        );
    }

    /**
     * @return array<string, array{0: float, 1: float, 2: int, 3: int}>
     */
    public function invalidDataProvider(): array
    {
        return [
            'negative home team ranking' => [-1.0, 90.0, 20, 10],
            'negative away team ranking' => [90.0, -1.0, 20, 10],
            'negative home score' => [90.0, 85.0, -5, 10],
            'negative away score' => [90.0, 85.0, 20, -5],
            'all negative values' => [-1.0, -1.0, -5, -5]
        ];
    }

}
