<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use App\Service\ResetTeams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/api', name: 'api_')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly ResetTeams $resetTeamsService
    ) {
    }

    #[Route('/teams', name: 'list_teams', methods: ['GET'])]
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
