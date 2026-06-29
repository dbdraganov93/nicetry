<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UsageController
{
    #[Route('/v1/usage', name: 'usage_current', methods: ['GET'])]
    public function currentUser(Request $request): JsonResponse
    {
        return ApiResponse::json([
            'period' => gmdate('Y-m'),
            'requests' => 0,
            'bytes_in' => 0,
            'bytes_out' => 0,
            'countries' => [],
            'errors' => 0,
        ]);
    }
}
