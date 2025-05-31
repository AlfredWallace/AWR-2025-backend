<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Users', description: 'Operations related to users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/users', name: 'list_users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        description: 'Returns a list of all users in the system',
        summary: 'List all users'
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'users', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'))
                    ],
                    type: 'object'
                ))
            ]
        )
    )]
    public function listUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return $this->json(['users' => $users]);
    }

    #[Route('/users/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users/register',
        description: 'Registers a new user in the system',
        summary: 'Register a new user'
    )]
    #[OA\RequestBody(
        description: 'User registration data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'password', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - missing required fields',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Response(
        response: 409,
        description: 'Conflict - user already exists',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Username and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return $this->json([
                'message' => 'User already exists'
            ], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setUsername($data['username']);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        // Set default roles
        $user->setRoles(['ROLE_USER']);

        // Save user to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully'
        ], Response::HTTP_CREATED);
    }
}
