<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiAuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Normalement, cette méthode ne sera PAS exécutée :
        // la sécurité (json_login) intercepte la requête avant.
        // Mais la route évite le 404 du routeur.
        return $this->json([
            'success' => false,
            'error' => 'Login endpoint should be handled by security firewall.',
        ], 500);
    }
}
