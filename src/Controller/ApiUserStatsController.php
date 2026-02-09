<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class ApiUserStatsController extends AbstractController
{
    #[Route('/{id}/stats', name: 'api_user_stats', methods: ['GET'])]
    public function stats(
        int $id,
        UserRepository $userRepository,
        TaskRepository $taskRepository
    ): JsonResponse {
        /** @var User|null $me */
        $me = $this->getUser();
        if (!$me) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $isAdmin = in_array('ROLE_ADMIN', $me->getRoles(), true);

        // sécurité: un user ne peut voir QUE ses stats, admin peut tout voir
        if (!$isAdmin && $me->getId() !== $id) {
            return $this->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $target = $userRepository->find($id);
        if (!$target) {
            return $this->json(['success' => false, 'error' => 'User not found'], 404);
        }

        $createdCount = $taskRepository->count(['createdBy' => $target]);
        $doneCount = $taskRepository->count(['doneBy' => $target]);

        return $this->json([
            'success' => true,
            'user' => [
                'id' => $target->getId(),
                'email' => $target->getUserIdentifier(),
            ],
            'stats' => [
                'tasksCreated' => $createdCount,
                'tasksDone' => $doneCount,
            ],
        ], 200);
    }
}
