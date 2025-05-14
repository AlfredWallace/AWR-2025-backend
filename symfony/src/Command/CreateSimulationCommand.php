<?php

namespace App\Command;

use App\Entity\RugbyMatch;
use App\Entity\Simulation;
use App\Repository\TeamRepository;
use App\Service\SimulationRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-simulation',
    description: 'Create a new simulation with 2 matches using the first 2 teams from the database',
)]
class CreateSimulationCommand extends Command
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SimulationRunner $simulationRunner
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->section('Creating a new simulation with 2 matches');
        
        try {
            // Fetch the first 2 teams from the database
            $teams = $this->teamRepository->findBy([], ['id' => 'ASC'], 2);
            
            if (count($teams) < 2) {
                $io->error('Not enough teams in the database. Need at least 2 teams.');
                return Command::FAILURE;
            }
            
            $team1 = $teams[0];
            $team2 = $teams[1];
            
            $io->text(sprintf('Using teams: %s and %s', $team1->name, $team2->name));
            
            // Create a new simulation
            $simulation = new Simulation('Test Simulation');
            
            // Create first match: team1 (home) vs team2 (away)
            $match1 = new RugbyMatch(
                $team1,      // homeTeam
                $team2,      // awayTeam
                25,          // homeScore
                20,          // awayScore
                false,       // isNeutralGround
                false,       // isWorldCup
                1,           // order
                $simulation  // simulation
            );
            
            // Create second match: team2 (home) vs team1 (away)
            $match2 = new RugbyMatch(
                $team2,      // homeTeam
                $team1,      // awayTeam
                22,          // homeScore
                18,          // awayScore
                false,       // isNeutralGround
                false,       // isWorldCup
                2,           // order
                $simulation  // simulation
            );
            
            // Add matches to simulation
            $simulation->addMatch($match1);
            $simulation->addMatch($match2);
            
            // Persist the simulation and matches
            $this->entityManager->persist($simulation);
            $this->entityManager->persist($match1);
            $this->entityManager->persist($match2);
            $this->entityManager->flush();
            
            $io->text('Simulation and matches created. Running simulation...');
            
            // Run the simulation
            $this->simulationRunner->run($simulation);
            
            $io->success(sprintf(
                'Simulation created and run successfully! Simulation ID: %d',
                $simulation->id
            ));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}