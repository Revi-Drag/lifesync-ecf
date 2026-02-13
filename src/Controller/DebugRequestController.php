<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DebugRequestController
{
    #[Route('/api/_debug/request', name: 'api_debug_request', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([
            'content_type' => $request->headers->get('content-type'),
            'content_length' => $request->headers->get('content-length'),
            'raw_length' => strlen($request->getContent() ?? ''),
            'raw' => $request->getContent(),
            'parsed' => $request->toArray(), // va throw si invalid JSON
        ]);
    }
}
