<?php

namespace App\Controller;

use App\Entity\Simulation;
use App\Exception\InvalidMatchDataException;
use App\Service\DatabaseUserProvider;
use App\Service\MatchFactory;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly SimulationRunner $simulationRunner,
        private readonly MatchFactory $matchFactory,
    ) {
    }

    #[Route('/simulations/run', name: 'run_simulation', methods: ['POST'])]
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
