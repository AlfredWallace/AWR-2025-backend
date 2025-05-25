<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class DatabaseUserProvider
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserRepository $userRepository
    ) {
    }

    public function getUser(): User
    {
        $storageUser = $this->tokenStorage->getToken()->getUser();
        if ($storageUser === null) {
            throw new \Exception('User not logged in');
        }

        if ($storageUser instanceof User) {
            return $storageUser;
        }

        if ($storageUser instanceof UserInterface) {
            return $this->userRepository->findOneBy(['username' => $storageUser->getUserIdentifier()]);
        }

        throw new \Exception('User of unknown type');
    }
}