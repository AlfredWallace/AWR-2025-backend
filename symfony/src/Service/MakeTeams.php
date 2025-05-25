<?php

namespace App\Service;

use App\Entity\Team;
use App\Exception\TeamValidationException;
use App\Repository\TeamRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class MakeTeams
{
    public function __construct(
        public TeamRepository $teamRepository,
        public ValidatorInterface $validator
    ) {
    }

    public function persistTeams(array $teamsData): void
    {
        foreach ($teamsData as $teamData) {
            $this->validateTeamDataStructure($teamData);

            $team = new Team();
            $team->setName($teamData['team']['name'])
                ->setAbbreviation($teamData['team']['abbreviation'])
                ->setExternalId($teamData['team']['id'])
                ->setExternalAltId($teamData['team']['altId'])
                ->setCountryCode($teamData['team']['countryCode'])
                ->setPoints($teamData['pts'])
                ->setPreviousPoints($teamData['previousPts']);

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
