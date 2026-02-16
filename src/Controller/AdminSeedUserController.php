<?php

namespace App\Controller;


use App\Entity\User;
use App\Security\DebugTokenGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class AdminSeedUserController
{
    #[Route('/admin/_seed_user', name: 'app_adminseeduser_seed', methods: ['POST'])]
    public function seed(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        DebugTokenGuard $guard
    ): JsonResponse {
        // Header token required
        $guard->assertValid($request, 'SEED_TOKEN', 'X-SEED-TOKEN');
        $required = ['ADMIN_EMAIL', 'ADMIN_PASSWORD', 'USER_EMAIL', 'USER_PASSWORD'];
        $missing = array_values(array_filter($required, fn($k) => empty($_ENV[$k] ?? null)));

        if ($missing) {
            return new JsonResponse(['ok' => false, 'error' => 'Missing env vars', 'missing' => $missing,], 500);
        }

        $results = [];

        // --- ADMIN ---
        $results['admin'] = $this->createUserIfNotExists(
            $em,
            $hasher,
            $_ENV['ADMIN_EMAIL'],
            $_ENV['ADMIN_PASSWORD'],
            ['ROLE_ADMIN']
        );

        // --- USER ---
        $results['user'] = $this->createUserIfNotExists(
            $em,
            $hasher,
            $_ENV['USER_EMAIL'],
            $_ENV['USER_PASSWORD'],
            ['ROLE_USER']
        );

        return new JsonResponse([
            'ok' => true,
            'created' => $results,
        ]);
    }

    private function createUserIfNotExists(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        string $email,
        string $plainPassword,
        array $roles
    ): string {
        $repo = $em->getRepository(User::class);

        $existing = $repo->findOneBy(['email' => $email]);
        if ($existing) {
            return "already_exists";
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);

        $user->setPassword(
            $hasher->hashPassword($user, $plainPassword)
        );

        $em->persist($user);
        $em->flush();

        return "created";
    }
}
