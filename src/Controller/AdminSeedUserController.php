<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminSeedUserController
{
    #[Route('/admin/_seed_user', methods: ['POST'])]
    public function seed(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        // Token protection
        $token = $request->headers->get('X-SEED-TOKEN');
        if ($token !== $_ENV['SEED_TOKEN']) {
            return new JsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
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
