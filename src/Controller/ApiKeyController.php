<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ApiKeyController
{
    public function create(Request $request): JsonResponse
    {
        return ApiResponse::json([
            'id' => bin2hex(random_bytes(16)),
            'prefix' => 'gp_' . bin2hex(random_bytes(4)),
            'secret' => 'gp_live_' . bin2hex(random_bytes(24)),
            'created_at' => gmdate(DATE_ATOM),
        ], 201);
    }

    public function delete(Request $request): JsonResponse
    {
        return ApiResponse::json(['deleted' => true]);
    }
}
