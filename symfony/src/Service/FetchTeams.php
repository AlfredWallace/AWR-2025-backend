<?php

namespace App\Service;

use App\Exception\TeamValidationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class FetchTeams
{
    public function __construct(
        private HttpClientInterface $client,
        private string $worldRugbyApiEndpoint
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TeamValidationException
     */
    public function fetchTeamsFromApi(): array
    {
        $response = $this->client->request('GET', $this->worldRugbyApiEndpoint);
        $content = $response->toArray();

        if (!array_key_exists('entries', $content)) {
            throw new TeamValidationException("Missing 'entries' key in API response", context: $content);
        }

        return $content['entries'];
    }
}

