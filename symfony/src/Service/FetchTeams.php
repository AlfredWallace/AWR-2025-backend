<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Exception\TeamValidationException;

readonly class FetchTeams
{
    public function __construct(
        private HttpClientInterface $client
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
    public function fetchTeamsFromApi(string $apiUrl): array
    {
        $response = $this->client->request('GET', $apiUrl);
        $content = $response->toArray();

        if (!array_key_exists('entries', $content)) {
            throw new TeamValidationException("Missing 'entries' key in API response", context: $content);
        }

        return $content['entries'];
    }
}

