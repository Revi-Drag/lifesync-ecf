<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AdminSeedUserController
{
    #[Route('/admin/_seed_user', name: 'admin_seed_user', methods: ['POST'])]
    public function seed(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $token = $request->headers->get('X-SEED-TOKEN');
        $expected = $_SERVER['SEED_TOKEN'] ?? getenv('SEED_TOKEN') ?: null;

        if (!$expected || !$token || !hash_equals($expected, $token)) {
            return new JsonResponse(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $email = $data['email'] ?? ($_ENV['SEED_EMAIL'] ?? null);
        $plainPassword = $data['password'] ?? ($_ENV['SEED_PASSWORD'] ?? null);

        if (!$email || !$plainPassword) {
            return new JsonResponse(['ok' => false, 'error' => 'Missing email/password'], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            return new JsonResponse(['ok' => true, 'created' => false, 'email' => $email]);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['ok' => true, 'created' => true, 'email' => $email]);
    }
}
