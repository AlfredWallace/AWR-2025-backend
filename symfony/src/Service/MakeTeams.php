<?php

namespace App\Service;

use App\Entity\Team;
use App\Exception\TeamValidationException;
use App\Repository\TeamRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class MakeTeams
{
    public function __construct(
        public TeamRepository     $teamRepository,
        public ValidatorInterface $validator
    )
    {
    }

    public function persistTeams(array $teamsData): void
    {
        foreach ($teamsData as $teamData) {
            $this->validateTeamDataStructure($teamData);

            $team = new Team(
                name: $teamData['team']['name'],
                abbreviation: $teamData['team']['abbreviation'],
                externalId: $teamData['team']['id'],
                externalAltId: $teamData['team']['altId'],
                countryCode: $teamData['team']['countryCode'],
                points: $teamData['pts'],
                previousPoints: $teamData['previousPts']
            );

            // Validate the team values
            $violations = $this->validator->validate($team);

            if ($violations->count() > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }

                throw new TeamValidationException("Team validation failed: ", context: $errors);
            }

            $this->teamRepository->getEntityManager()->persist($team);
        }
    }

    private function validateTeamDataStructure(array $teamData): void
    {
        $requiredKeys = ['team', 'pts', 'previousPts'];
        $teamKeys = ['name', 'abbreviation', 'id', 'altId', 'countryCode'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $teamData)) {
                throw new TeamValidationException("Missing required key: $key", context: $teamData);
            }
        }

        foreach ($teamKeys as $key) {
            if (!array_key_exists($key, $teamData['team'])) {
                throw new TeamValidationException("Missing required team key: $key", context: $teamData);
            }
        }
    }
}
