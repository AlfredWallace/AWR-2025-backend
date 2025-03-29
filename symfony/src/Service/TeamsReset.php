<?php

namespace App\Service;

use App\Entity\Team;
use App\Exception\TeamValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class TeamsReset
{
    public function __construct(
        public HttpClientInterface    $client,
        public EntityManagerInterface $entityManager,
        public ValidatorInterface     $validator,
        public TruncateTeams          $truncateTeams,
        public string                 $apiUrl
    ) {
    }

    public function fetchAndPersistTeams(): void
    {
        // 1. Clear existing team data
        $this->truncateTeams->clearExistingTeams();

        // 2. Fetch data from the World Rugby API
        $teamsData = $this->fetchTeamsFromApi();

        // 3. Persist the new data
        $this->persistTeams($teamsData);

        // 4. Flush all changes
        $this->entityManager->flush();
    }

    private function fetchTeamsFromApi(): array
    {
        $response = $this->client->request('GET', $this->apiUrl);
        $content = $response->toArray();

        return $content['entries'];
    }

    private function persistTeams(array $teamsData): void
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

            $this->entityManager->persist($team);
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

