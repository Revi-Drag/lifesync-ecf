<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiMeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated.',
            ], 401);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'email' => $user?->getUserIdentifier(),
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ],
        ]);
    }
}
