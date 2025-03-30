<?php

namespace App\Service;

use App\Entity\Team;
use App\Exception\TeamValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class ResetTeams
{
    public function __construct(
        public FetchTeams             $fetchTeams,
        public EntityManagerInterface $entityManager,
        public ValidatorInterface     $validator,
        public TruncateTeams          $truncateTeams,
        public string                 $apiUrl
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function resetTeams(): void
    {
        // 1. Clear existing team data
        $this->truncateTeams->clearExistingTeams();

        // 2. Fetch data from the World Rugby API
        $teamsData = $this->fetchTeams->fetchTeamsFromApi($this->apiUrl);

        // 3. Persist the new data
        $this->persistTeams($teamsData);

        // 4. Flush all changes
        $this->entityManager->flush();
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
