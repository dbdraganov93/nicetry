<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UsageController
{
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
