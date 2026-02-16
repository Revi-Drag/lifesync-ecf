<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class DebugTokenGuard
{
    public function assertValid(Request $request, string $envKey, string $headerName): void
    {
        $expected = $_ENV[$envKey] ?? null;
        $provided = $request->headers->get($headerName);

        if (!$expected) {
            
            throw new AccessDeniedHttpException('Debug/seed disabled (missing env token).');
        }

        if (!$provided || !hash_equals($expected, $provided)) {
            throw new AccessDeniedHttpException('Forbidden.');
        }
    }
}