<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DebugDbController
{
    #[Route('/debug/db', name: 'debug_db', methods: ['GET'])]
    public function __invoke(Connection $conn): JsonResponse
    {
        return new JsonResponse([
            'ok' => true,
            'database' => $conn->fetchOne('SELECT current_database()'),
        ]);
    }
}
