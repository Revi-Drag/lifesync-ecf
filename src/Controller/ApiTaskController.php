<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
class ApiTaskController extends AbstractController
{
    #[Route('', name: 'api_tasks_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $tasks = $taskRepository->findBy(
            ['createdBy' => $user],
            ['createdAt' => 'DESC']
        );

        $data = array_map([$this, 'serializeTask'], $tasks);

        return $this->json(['success' => true, 'tasks' => $data], 200);
    }

    #[Route('', name: 'api_tasks_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $payload = $this->getJson($request);
        if ($payload === null) {
            return $this->json(['success' => false, 'error' => 'Invalid JSON'], 400);
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $description = isset($payload['description']) ? (string) $payload['description'] : null;
        $difficulty = $payload['difficulty'] ?? null;
        $durationMinutes = $payload['durationMinutes'] ?? null;

        $errors = [];
        if ($title === '' || mb_strlen($title) < 2) {
            $errors['title'] = 'Title must be at least 2 characters.';
        }

        if (!is_int($difficulty)) {
            $errors['difficulty'] = 'Difficulty must be an integer.';
        } elseif ($difficulty < 1 || $difficulty > 5) {
            $errors['difficulty'] = 'Difficulty must be between 1 and 5.';
        }

        if (!is_int($durationMinutes)) {
            $errors['durationMinutes'] = 'Duration must be an integer (minutes).';
        } elseif ($durationMinutes < 1 || $durationMinutes > 1440) {
            $errors['durationMinutes'] = 'Duration must be between 1 and 1440 minutes.';
        }

        if ($errors) {
            return $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $task = new Task();
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setDifficulty($difficulty);
        $task->setDurationMinutes($durationMinutes);
        $task->setStatus('TODO');
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setCreatedBy($user);

        $em->persist($task);
        $em->flush();

        return $this->json(['success' => true, 'task' => $this->serializeTask($task)], 201);
    }

    #[Route('/{id}', name: 'api_tasks_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $task = $taskRepository->find($id);
        if (!$task) {
            return $this->json(['success' => false, 'error' => 'Task not found'], 404);
        }

        // Ownership check
        if ($task->getCreatedBy()?->getUserIdentifier() !== $user->getUserIdentifier()) {
            return $this->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $payload = $this->getJson($request);
        if ($payload === null) {
            return $this->json(['success' => false, 'error' => 'Invalid JSON'], 400);
        }

        $allowedStatuses = ['TODO', 'IN_PROGRESS', 'DONE'];

        if (array_key_exists('status', $payload)) {
            $status = (string) $payload['status'];
            if (!in_array($status, $allowedStatuses, true)) {
                return $this->json([
                    'success' => false,
                    'errors' => ['status' => 'Status must be one of TODO, IN_PROGRESS, DONE.']
                ], 422);
            }
            $task->setStatus($status);
        }

        // Optionnel: autoriser modifications basiques
        if (array_key_exists('title', $payload)) {
            $title = trim((string) $payload['title']);
            if ($title === '' || mb_strlen($title) < 2) {
                return $this->json(['success' => false, 'errors' => ['title' => 'Title must be at least 2 characters.']], 422);
            }
            $task->setTitle($title);
        }

        if (array_key_exists('description', $payload)) {
            $task->setDescription($payload['description'] !== null ? (string) $payload['description'] : null);
        }

        if (array_key_exists('difficulty', $payload)) {
            $difficulty = $payload['difficulty'];
            if (!is_int($difficulty) || $difficulty < 1 || $difficulty > 5) {
                return $this->json(['success' => false, 'errors' => ['difficulty' => 'Difficulty must be an integer between 1 and 5.']], 422);
            }
            $task->setDifficulty($difficulty);
        }

        if (array_key_exists('durationMinutes', $payload)) {
            $durationMinutes = $payload['durationMinutes'];
            if (!is_int($durationMinutes) || $durationMinutes < 1 || $durationMinutes > 1440) {
                return $this->json(['success' => false, 'errors' => ['durationMinutes' => 'Duration must be an integer between 1 and 1440.']], 422);
            }
            $task->setDurationMinutes($durationMinutes);
        }

        $em->flush();

        return $this->json(['success' => true, 'task' => $this->serializeTask($task)], 200);
    }

    #[Route('/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    public function delete(int $id, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $task = $taskRepository->find($id);
        if (!$task) {
            return $this->json(['success' => false, 'error' => 'Task not found'], 404);
        }

        // Ownership check
        if ($task->getCreatedBy()?->getUserIdentifier() !== $user->getUserIdentifier()) {
            return $this->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $em->remove($task);
        $em->flush();

        return $this->json(['success' => true], 200);
    }

    private function getJson(Request $request): ?array
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function serializeTask(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'difficulty' => $task->getDifficulty(),
            'durationMinutes' => $task->getDurationMinutes(),
            'status' => $task->getStatus(),
            'createdAt' => $task->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'createdBy' => $task->getCreatedBy()?->getUserIdentifier(),
        ];
    }
}
