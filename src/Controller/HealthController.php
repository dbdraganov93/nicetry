<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class HealthController
{
    public function health(Request $request): JsonResponse
    {
        return ApiResponse::json(['status' => 'ok', 'service' => 'geo-proxy-gateway']);
    }
}
