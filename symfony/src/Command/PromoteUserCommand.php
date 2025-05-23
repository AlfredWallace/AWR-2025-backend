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

#[AsCommand(
    name: 'app:promote-user',
    description: 'Give admin rights to a specific user',
)]
class PromoteUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user to promote');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        $io->section(sprintf('Promoting user "%s" to admin', $username));

        try {
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $io->error(sprintf('User "%s" not found.', $username));
                return Command::FAILURE;
            }

            $roles = $user->getRoles();

            // Check if user already has ROLE_ADMIN
            if (in_array('ROLE_ADMIN', $roles)) {
                $io->warning(sprintf('User "%s" already has admin rights.', $username));
                return Command::SUCCESS;
            }

            // Add ROLE_ADMIN to user's roles
            $roles[] = 'ROLE_ADMIN';

            /**
             * This line performs several important operations when setting the user's roles:
             * 1. array_filter($roles, fn($role) => $role !== 'ROLE_USER') - Removes 'ROLE_USER' from the stored roles
             *    because the User entity automatically adds this role to all users in the getRoles() method.
             *    Storing it in the database would be redundant.
             * 
             * 2. array_unique(...) - Removes any duplicate roles that might exist in the array
             *    to ensure each role appears only once.
             * 
             * 3. array_values(...) - Reindexes the array to ensure it has sequential numeric keys
             *    after filtering and removing duplicates, which maintains a clean array structure.
             * 
             * This approach ensures that roles are stored efficiently in the database while
             * maintaining the security model where every user implicitly has ROLE_USER.
             */
            $user->setRoles(array_values(array_unique(array_filter($roles, fn($role) => $role !== 'ROLE_USER'))));

            // Save changes
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success(sprintf('User "%s" has been promoted to admin.', $username));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred while promoting user: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
