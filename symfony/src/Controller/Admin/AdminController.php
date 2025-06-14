<?php

namespace App\Controller\Admin;

use App\Form\AdminLoginType;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly TeamRepository $teamRepository,
    ) {
    }

    #[Route('/login', name: 'login')]
    public function login(Request $request): Response
    {
        $form = $this->createForm(AdminLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $username = $data['username'];
            $password = $data['password'];

            try {
                // Find user by username
                $user = $this->userRepository->findOneBy(['username' => $username]);

                if (!$user) {
                    throw new UserNotFoundException('Invalid credentials');
                }

                // Check if user has admin role
                if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                    throw new AccessDeniedException('Access denied');
                }

                // Verify password
                if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                    throw new BadCredentialsException('Invalid credentials');
                }

                // Generate JWT token
                $token = $this->jwtManager->create($user);

                // Create response with redirect
                $response = $this->redirectToRoute('admin_dashboard');

                // Set JWT token as cookie
                $cookie = new Cookie(
                    'BEARER',           // Cookie name
                    $token,             // Cookie value
                    time() + 36000,     // Expiration (10 hours, matching JWT TTL)
                    '/',                // Path
                    null,               // Domain
                    true,               // Secure
                    true,               // HttpOnly
                    false,              // Raw
                    'strict'            // SameSite
                );

                $response->headers->setCookie($cookie);

                return $response;
            } catch (AuthenticationException|AccessDeniedException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'teams' => $this->teamRepository->findBy([], ['points' => 'DESC']),
        ]);
    }
}
