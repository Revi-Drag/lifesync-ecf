<?php

namespace App\Controller;

use App\Security\DebugTokenGuard;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DebugDbController
{
    #[Route('/debug/db', name: 'debug_db', methods: ['GET'])]
    public function __invoke(Request $request, Connection $conn, DebugTokenGuard $guard): JsonResponse
    {
        $guard->assertValid($request, 'DEBUG_TOKEN', 'X-DEBUG-TOKEN');

        return new JsonResponse([
            'ok' => true,
            'database' => $conn->fetchOne('SELECT current_database()'),
        ]);
    }
}
