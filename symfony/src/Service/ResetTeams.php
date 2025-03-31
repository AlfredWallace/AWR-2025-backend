<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
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
        public MakeTeams              $makeTeams,
        public TruncateTeams          $truncateTeams,
        public string                 $apiUrl
    )
    {
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
        $this->makeTeams->persistTeams($teamsData);

        // 4. Flush all changes
        $this->entityManager->flush();
    }
}
