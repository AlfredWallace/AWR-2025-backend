<?php

namespace App\Controller\Api;

use App\Repository\TeamRepository;
use App\Service\ResetTeams;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Teams', description: 'Operations related to rugby teams')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly ResetTeams $resetTeamsService
    ) {
    }

    #[Route('/teams', name: 'list_teams', methods: ['GET'])]
    #[OA\Get(
        path: '/api/teams',
        description: 'Returns a list of all rugby teams in the system',
        summary: 'List all teams'
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'teams', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'code', type: 'string'),
                        new OA\Property(property: 'rank', type: 'integer'),
                        new OA\Property(property: 'points', type: 'number', format: 'float')
                    ],
                    type: 'object'
                ))
            ]
        )
    )]
    public function listTeams(): JsonResponse
    {
        $teams = $this->teamRepository->findAll();
        return $this->json(['teams' => $teams]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/teams/reset', name: 'reset_teams', methods: ['POST'])]
    #[OA\Post(
        path: '/api/teams/reset',
        description: 'Resets all teams data to their initial state',
        summary: 'Reset teams data'
    )]
    #[OA\Response(
        response: 200,
        description: 'Teams successfully reset',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Teams have been successfully reset')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'An error occurred while resetting teams')
            ]
        )
    )]
    public function resetTeams(): JsonResponse
    {
        try {
            $this->resetTeamsService->resetTeams();
            return $this->json(['message' => 'Teams have been successfully reset']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred while resetting teams: ' . $e->getMessage()], 500);
        }
    }
}
