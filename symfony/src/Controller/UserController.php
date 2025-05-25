<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Users', description: 'Operations related to users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository
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
}
