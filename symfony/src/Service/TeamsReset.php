<?php

namespace App\Service;

use App\Entity\Team;
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
            $team = new Team(
                name: $teamData['team']['name'],
                abbreviation: $teamData['team']['abbreviation'],
                externalId: $teamData['team']['id'],
                externalAltId: $teamData['team']['altId'],
                countryCode: $teamData['team']['countryCode'],
                points: $teamData['pts'],
                previousPoints: $teamData['previousPts']
            );

            $errors = $this->validator->validate($team);

            if (count($errors) > 0) {
                // Handle validation errors
                // You might want to log the errors or throw an exception
                // based on your application's requirements
                $errorsString = (string) $errors;
                throw new \Exception("Team validation failed: ".$errorsString);
            }

            $this->entityManager->persist($team);
        }
    }
}

