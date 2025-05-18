<?php

namespace App\Tests;

use App\Entity\Simulation;
use App\Exception\InvalidMatchDataException;
use App\Repository\TeamRepository;
use App\Service\MatchFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MatchFactoryTest extends TestCase
{
    private MatchFactory $matchFactory;
    private TeamRepository $teamRepositoryMock;
    private Simulation $simulationMock;

    protected function setUp(): void
    {
        $this->teamRepositoryMock = $this->createMock(TeamRepository::class);
        $this->simulationMock = $this->createMock(Simulation::class);

        $this->matchFactory = new MatchFactory(
            $this->teamRepositoryMock,
            $this->createMock(SerializerInterface::class),
            $this->createMock(ValidatorInterface::class)
        );
    }

    /**
     * @dataProvider invalidMatchDataProvider
     */
    public function test_validate_match_data_with_invalid_data(array $matchData, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidMatchDataException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        // We don't need to set up the team repository mock because the validation should fail before it's used

        $this->matchFactory->createMatch($matchData, $this->simulationMock, 1);
    }

    public function test_get_team_throws_exception_when_team_not_found(): void
    {
        $matchData = [
            'homeTeamId' => 999, // Non-existent team ID
            'awayTeamId' => 2,
            'homeScore' => 10,
            'awayScore' => 5
        ];

        // Set up team repository mock to return null for non-existent team
        $this->teamRepositoryMock->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(InvalidMatchDataException::class);
        $this->expectExceptionMessage('Team not found');

        $this->matchFactory->createMatch($matchData, $this->simulationMock, 1);
    }

    public function invalidMatchDataProvider(): array
    {
        return [
            'empty match data' => [
                'matchData' => [],
                'expectedExceptionMessage' => 'Missing required key: homeTeamId'
            ],
            'missing homeTeamId' => [
                'matchData' => [
                    'awayTeamId' => 2,
                    'homeScore' => 10,
                    'awayScore' => 5
                ],
                'expectedExceptionMessage' => 'Missing required key: homeTeamId'
            ],
            'missing awayTeamId' => [
                'matchData' => [
                    'homeTeamId' => 1,
                    'homeScore' => 10,
                    'awayScore' => 5
                ],
                'expectedExceptionMessage' => 'Missing required key: awayTeamId'
            ],
            'missing homeScore' => [
                'matchData' => [
                    'homeTeamId' => 1,
                    'awayTeamId' => 2,
                    'awayScore' => 5
                ],
                'expectedExceptionMessage' => 'Missing required key: homeScore'
            ],
            'missing awayScore' => [
                'matchData' => [
                    'homeTeamId' => 1,
                    'awayTeamId' => 2,
                    'homeScore' => 10
                ],
                'expectedExceptionMessage' => 'Missing required key: awayScore'
            ]
        ];
    }
}
