<?php

namespace App\Service;

use App\Entity\RugbyMatch;
use App\Entity\Simulation;
use App\Entity\Team;
use App\Exception\InvalidMatchDataException;
use App\Repository\TeamRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class MatchFactory
{
    public function __construct(
        private TeamRepository $teamRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Create a RugbyMatch from match data
     *
     * @param array $matchData The match data from the request
     * @param Simulation $simulation The simulation this match belongs to
     * @param int $stepNumber The step number for this match
     * @return RugbyMatch The created match
     * @throws InvalidMatchDataException If the match data is invalid or validation fails
     */
    public function createMatch(array $matchData, Simulation $simulation, int $stepNumber): RugbyMatch
    {
        // Check if matchData has all required keys and validate values
        $requiredKeys = ['homeTeamId', 'awayTeamId', 'homeScore', 'awayScore'];

        $this->validateMatchData($matchData, $requiredKeys);

        // Find teams
        $homeTeam = $this->getTeam($matchData['homeTeamId']);
        $awayTeam = $this->getTeam($matchData['awayTeamId']);

        // Prepare match data for deserialization
        $matchData['homeTeam'] = $homeTeam;
        $matchData['awayTeam'] = $awayTeam;
        $matchData['stepNumber'] = $stepNumber;
        $matchData['simulation'] = $simulation;

        // Deserialize match data into RugbyMatch object
        $match = $this->serializer->denormalize($matchData, RugbyMatch::class);

        // Validate the match
        $violations = $this->validator->validate($match);

        if (count($violations) > 0) {
            // Throw exception for the first violation encountered
            $violation = $violations[0];
            throw new InvalidMatchDataException(
                'Validation failed: ' . $violation->getMessage(),
                0,
                null,
                ['violation' => $violation->getMessage()]
            );
        }

        return $match;
    }

    /**
     * Get a team by ID or throw an exception if not found
     *
     * @param int $teamId The team ID to look up
     * @return Team The found team
     * @throws InvalidMatchDataException If the team is not found
     */
    private function getTeam(int $teamId): Team
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new InvalidMatchDataException(
                "Team not found",
                0,
                null,
                ['id' => $teamId]
            );
        }

        return $team;
    }

    /**
     * Validate match data against required keys
     *
     * @param array $matchData The match data to validate
     * @param array $requiredKeys The keys that must exist in the match data
     * @throws InvalidMatchDataException If validation fails
     */
    private function validateMatchData(array $matchData, array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            // Check if key exists
            if (!isset($matchData[$key])) {
                throw new InvalidMatchDataException(
                    'Missing required key: ' . $key,
                    0,
                    null,
                    ['missingKey' => $key, 'requiredKeys' => $requiredKeys]
                );
            }
        }
    }
}
