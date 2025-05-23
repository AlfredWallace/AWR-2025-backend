<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new user with admin rights',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username for the new admin user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password for the new admin user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        
        $io->section('Creating new admin user');
        
        try {
            // Check if user already exists
            $existingUser = $this->userRepository->findOneBy(['username' => $username]);
            if ($existingUser) {
                $io->error(sprintf('User "%s" already exists.', $username));
                return Command::FAILURE;
            }
            
            // Create new user
            $user = new User();
            $user->setUsername($username);
            
            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);
            
            // Set roles with ROLE_ADMIN
            $user->setRoles(['ROLE_ADMIN']);
            
            // Save user to database
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $io->success(sprintf('Admin user "%s" has been created successfully.', $username));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred while creating admin user: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}