<?php

namespace App\Command;

use App\Service\ResetTeams;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:reset-teams',
    description: 'Reset all teams by clearing existing data and fetching fresh data from the World Rugby API',
)]
class ResetTeamsCommand extends Command
{
    public function __construct(
        private readonly ResetTeams $resetTeamsService
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->section('Starting team reset process');
        
        try {
            $io->text('Clearing existing team data...');
            
            // Call the resetTeams service method
            $this->resetTeamsService->resetTeams();
            
            $io->success('Teams have been successfully reset!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred while resetting teams: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
