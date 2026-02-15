<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DebugUserController
{
    #[Route('/debug/user', name: 'debug_user', methods: ['GET'])]
    public function user(Request $request, Connection $db): JsonResponse
    {
        $email = $request->query->get('email');
        if (!$email) {
            return new JsonResponse(['ok' => false, 'error' => 'missing email'], 400);
        }

        // table est `user` (avec backticks côté Doctrine), côté PG c'est "user" si quoted.
        // On teste les 2.
        $queries = [
            'unquoted_user' => 'SELECT id, email, roles, password FROM "user" WHERE email = :email LIMIT 1',
            'backtick_style' => 'SELECT id, email, roles, password FROM "user" WHERE email = :email LIMIT 1',
        ];

        foreach ($queries as $k => $sql) {
            try {
                $row = $db->fetchAssociative($sql, ['email' => $email]);
                if ($row) {
                    // On ne renvoie pas le hash complet
                    $row['password'] = substr((string) $row['password'], 0, 20) . '...';
                    return new JsonResponse(['ok' => true, 'source' => $k, 'user' => $row]);
                }
            } catch (\Throwable $e) {
                return new JsonResponse(['ok' => false, 'source' => $k, 'error' => $e->getMessage()], 500);
            }
        }

        return new JsonResponse(['ok' => true, 'user' => null]);
    }
}
