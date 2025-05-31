<?php

namespace App\Controller\Api;

use App\Entity\Simulation;
use App\Exception\InvalidMatchDataException;
use App\Service\DatabaseUserProvider;
use App\Service\MatchFactory;
use App\Service\SimulationRunner;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Simulations', description: 'Operations related to rugby match simulations')]
class SimulationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SimulationRunner $simulationRunner,
        private readonly MatchFactory $matchFactory,
    ) {
    }

    #[Route('/simulations/run', name: 'run_simulation', methods: ['POST'])]
    #[OA\Post(
        path: '/api/simulations/run',
        description: 'Creates and runs a new simulation with the provided match data',
        summary: 'Run a new simulation'
    )]
    #[OA\RequestBody(
        description: 'Simulation data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', description: 'Name of the simulation', type: 'string'),
                new OA\Property(
                    property: 'matches', description: 'Array of match data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'homeTeamId', description: 'Home team ID', type: 'integer'),
                            new OA\Property(property: 'awayTeamId', description: 'Away team ID', type: 'integer'),
                            new OA\Property(property: 'homeScore', description: 'Home team score', type: 'integer'),
                            new OA\Property(property: 'awayScore', description: 'Away team score', type: 'integer'),
                            new OA\Property(
                                property: 'isNeutralGround',
                                description: 'Whether the match is played at a neutral venue',
                                type: 'boolean',
                                default: false
                            ),
                            new OA\Property(
                                property: 'isWorldCup',
                                description: 'Whether the match is part of the Rugby World Cup',
                                type: 'boolean',
                                default: false
                            )
                        ],
                        type: 'object'
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Simulation created and run successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Simulation created and run successfully'),
                new OA\Property(property: 'simulationId', type: 'integer', example: 123)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request data',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Invalid request data. Required fields: name, matches (array)'),
                new OA\Property(property: 'context', type: 'object', nullable: true)
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'An error occurred')
            ]
        )
    )]
    public function runSimulation(Request $request, DatabaseUserProvider $databaseUserProvider): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['name']) || !isset($data['matches']) || !is_array($data['matches'])) {
                return $this->json([
                    'error' => 'Invalid request data. Required fields: name, matches (array)'
                ], Response::HTTP_BAD_REQUEST);
            }

            $simulation = new Simulation();
            $simulation->setName($data['name']);
            $simulation->setUser($databaseUserProvider->getUser());

            foreach ($data['matches'] as $index => $matchData) {
                $match = $this->matchFactory->createMatch($matchData, $simulation, $index + 1);
                $simulation->addMatch($match);
                $this->entityManager->persist($match);
            }

            $this->entityManager->persist($simulation);
            $this->entityManager->flush();

            $this->simulationRunner->runNewSimulation($simulation);

            return $this->json([
                'message' => 'Simulation created and run successfully',
                'simulationId' => $simulation->id
            ], Response::HTTP_CREATED);
        } catch (InvalidMatchDataException $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'context' => $e->context
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
