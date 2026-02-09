<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiMeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
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
                'id' => $user->getId(),
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
        ], 200);
    }

    #[Route('/api/me/stats', name: 'api_me_stats', methods: ['GET'])]
    public function stats(TaskRepository $taskRepository): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated.',
            ], 401);
        }

        $tasksCreated = $taskRepository->count(['createdBy' => $user]);

        $tasksDone = $taskRepository->count([
            'doneBy' => $user,
            'status' => 'DONE',
        ]);

        return $this->json([
            'success' => true,
            'stats' => [
                'tasksCreated' => $tasksCreated,
                'tasksDone' => $tasksDone,
            ],
        ], 200);
    }
}
