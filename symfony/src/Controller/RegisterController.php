<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Username and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return $this->json([
                'message' => 'User already exists'
            ], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setUsername($data['username']);

        // Hash the password
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        // Set default roles
        $user->setRoles(['ROLE_USER']);

        // Save user to database
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully'
        ], Response::HTTP_CREATED);
    }
}
