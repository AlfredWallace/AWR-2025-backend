<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-users',
    description: 'List all users in the system',
)]
class ListUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->section('Listing all users');
        
        try {
            $users = $this->userRepository->findAll();
            
            if (empty($users)) {
                $io->warning('No users found in the system.');
                return Command::SUCCESS;
            }
            
            $tableData = [];
            foreach ($users as $user) {
                $tableData[] = [
                    'ID' => $user->getId(),
                    'Username' => $user->getUsername(),
                    'Roles' => implode(', ', $user->getRoles()),
                ];
            }
            
            $io->table(
                ['ID', 'Username', 'Roles'],
                $tableData
            );
            
            $io->success(sprintf('Found %d user(s) in the system.', count($users)));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred while listing users: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}