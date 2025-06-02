<?php

namespace App\Controller\Admin;

use App\Form\AdminLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request): Response
    {
        $form = $this->createForm(AdminLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // In a real application, you would handle authentication here
            // For now, we're just creating the form structure

            // Example of what authentication might look like:
            // $data = $form->getData();
            // $username = $data['username'];
            // $password = $data['password'];
            // ... authenticate user ...

            // Redirect to dashboard after successful login
            // return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
}
