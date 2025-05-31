<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Authentication', description: 'Operations related to authentication')]
class AuthController extends AbstractController
{
    /**
     * This controller doesn't have any methods as the login_check route is handled by LexikJWTAuthenticationBundle.
     * It only exists to provide OpenAPI documentation for the login_check route.
     */

    #[Route('/login_check', name: 'login_check', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login_check',
        description: 'Authenticates a user and returns a JWT token',
        summary: 'Login and get JWT token'
    )]
    #[OA\RequestBody(
        description: 'User credentials',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'username', type: 'string', example: 'user'),
                new OA\Property(property: 'password', type: 'string', example: 'password')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'JWT token generated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.')
            ]
        )
    )]
    public function loginCheck()
    {
        // This method is never executed, it's just a placeholder for OpenAPI annotations
        // The actual login_check route is handled by LexikJWTAuthenticationBundle
        throw new \LogicException('This method should not be called.');
    }
}
