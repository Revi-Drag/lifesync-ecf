<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DebugRequestController
{
    #[Route('/api/_debug/request', name: 'debug_request', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $raw = $request->getContent(); // body brut

        $firstBytes = substr($raw, 0, 32);
        $hex = strtoupper(implode(' ', str_split(bin2hex($firstBytes), 2)));

        return new JsonResponse([
            'content_type' => $request->headers->get('content-type'),
            'len' => strlen($raw),
            'hex_first_32' => $hex,
            'raw_first_200' => substr($raw, 0, 200),
        ]);
    }
}
