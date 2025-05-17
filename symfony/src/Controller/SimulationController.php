<?php

namespace App\Controller;

use App\Entity\RugbyMatch;
use App\Entity\Simulation;
use App\Repository\TeamRepository;
use App\Service\SimulationRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class SimulationController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SimulationRunner $simulationRunner
    ) {
    }

    #[Route('/simulations/run', name: 'simulation_run', methods: ['POST'])]
    public function createSimulation(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['name']) || !isset($data['matches']) || !is_array($data['matches'])) {
                return $this->json([
                    'error' => 'Invalid request data. Required fields: name, matches (array)'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Create a new simulation
            $simulation = new Simulation($data['name']);
            
            // Process each match
            foreach ($data['matches'] as $index => $matchData) {
                // Validate match data
                if (!$this->validateMatchData($matchData)) {
                    return $this->json([
                        'error' => 'Invalid match data at index ' . $index
                    ], Response::HTTP_BAD_REQUEST);
                }
                
                // Find teams
                $homeTeam = $this->teamRepository->find($matchData['homeTeamId']);
                $awayTeam = $this->teamRepository->find($matchData['awayTeamId']);
                
                if (!$homeTeam || !$awayTeam) {
                    return $this->json([
                        'error' => 'Team not found at match index ' . $index
                    ], Response::HTTP_BAD_REQUEST);
                }
                
                // Create match
                $match = new RugbyMatch(
                    $homeTeam,
                    $awayTeam,
                    $matchData['homeScore'],
                    $matchData['awayScore'],
                    $matchData['isNeutralGround'] ?? false,
                    $matchData['isWorldCup'] ?? false,
                    $index + 1, // stepNumber
                    $simulation
                );
                
                // Add match to simulation
                $simulation->addMatch($match);
                
                // Persist match
                $this->entityManager->persist($match);
            }
            
            // Persist simulation
            $this->entityManager->persist($simulation);
            $this->entityManager->flush();
            
            $this->simulationRunner->runNewSimulation($simulation);

            // Return success response
            return $this->json([
                'success' => true,
                'message' => 'Simulation created and run successfully',
                'simulationId' => $simulation->id
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    private function validateMatchData(array $matchData): bool
    {
        return isset($matchData['homeTeamId']) &&
               isset($matchData['awayTeamId']) &&
               isset($matchData['homeScore']) &&
               isset($matchData['awayScore']) &&
               is_numeric($matchData['homeScore']) &&
               is_numeric($matchData['awayScore']) &&
               $matchData['homeScore'] >= 0 &&
               $matchData['awayScore'] >= 0;
    }
}