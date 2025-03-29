<?php

namespace App\Service;

use App\Entity\Team;
use App\Exception\TeamValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamsReset
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        string $apiUrl
    ) {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->apiUrl = $apiUrl;
    }

    public function fetchAndPersistTeams(): void
    {
        // 1. Clear existing team data
        $this->clearExistingTeams();

        // 2. Fetch data from the World Rugby API
        $teamsData = $this->fetchTeamsFromApi();

        // 3. Persist the new data
        $this->persistTeams($teamsData);

        // 4. Flush all changes
        $this->entityManager->flush();
    }

    private function clearExistingTeams(): void
    {
        $repository = $this->entityManager->getRepository(Team::class);
        $teams = $repository->findAll();

        foreach ($teams as $team) {
            $this->entityManager->remove($team);
        }
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

