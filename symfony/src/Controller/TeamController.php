<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teamRepository
    ) {
    }

    #[Route('/teams', name: 'list_teams', methods: ['GET'])]
    public function listTeams(): JsonResponse
    {
        $teams = $this->teamRepository->findAll();
        return $this->json(['teams' => $teams]);
    }
}