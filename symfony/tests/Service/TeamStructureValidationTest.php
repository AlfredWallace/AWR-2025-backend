<?php

namespace App\Tests\Service;

use App\Exception\TeamValidationException;
use App\Repository\TeamRepository;
use App\Service\MakeTeams;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamStructureValidationTest extends TestCase
{
    private MakeTeams $makeTeams;
    private TeamRepository $teamRepositoryMock;
    private ValidatorInterface $validatorMock;

    protected function setUp(): void
    {
        $this->teamRepositoryMock = $this->createMock(TeamRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->makeTeams = new MakeTeams(
            $this->teamRepositoryMock,
            $this->validatorMock
        );
    }

    /**
     * @dataProvider teamsDataProvider
     */
    public function testPersistTeamsStructureValidation(array $teamsData, string $expectedExceptionMessage): void
    {
        $this->expectException(TeamValidationException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->makeTeams->persistTeams($teamsData);
    }

    public function teamsDataProvider(): array
    {
        return [
            'missing team key' => [
                'teamsData' => [
                    ['pts' => 10, 'previousPts' => 5]
                ],
                'expectedExceptionMessage' => 'Missing required key: team'
            ],
            'missing pts key' => [
                'teamsData' => [
                    [
                        'team' => [
                            'name' => 'Team A',
                            'abbreviation' => 'TA',
                            'id' => 1,
                            'altId' => 2,
                            'countryCode' => 'US'
                        ],
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required key: pts'
            ],
            'missing previousPts key' => [
                'teamsData' => [
                    [
                        'team' => [
                            'name' => 'Team A',
                            'abbreviation' => 'TA',
                            'id' => 1,
                            'altId' => 2,
                            'countryCode' => 'US'
                        ],
                        'pts' => 10
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required key: previousPts'
            ],
            'missing team name key' => [
                'teamsData' => [
                    [
                        'team' => ['abbreviation' => 'TA', 'id' => 1, 'altId' => 2, 'countryCode' => 'US'],
                        'pts' => 10,
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required team key: name'
            ],
            'missing team abbreviation key' => [
                'teamsData' => [
                    [
                        'team' => ['name' => 'Team A', 'id' => 1, 'altId' => 2, 'countryCode' => 'US'],
                        'pts' => 10,
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required team key: abbreviation'
            ],
            'missing team id key' => [
                'teamsData' => [
                    [
                        'team' => ['name' => 'Team A', 'abbreviation' => 'TA', 'altId' => 2, 'countryCode' => 'US'],
                        'pts' => 10,
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required team key: id'
            ],
            'missing team altId key' => [
                'teamsData' => [
                    [
                        'team' => ['name' => 'Team A', 'abbreviation' => 'TA', 'id' => 1, 'countryCode' => 'US'],
                        'pts' => 10,
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required team key: altId'
            ],
            'missing team countryCode key' => [
                'teamsData' => [
                    [
                        'team' => ['name' => 'Team A', 'abbreviation' => 'TA', 'id' => 1, 'altId' => 2],
                        'pts' => 10,
                        'previousPts' => 5
                    ]
                ],
                'expectedExceptionMessage' => 'Missing required team key: countryCode'
            ],
        ];
    }
}

