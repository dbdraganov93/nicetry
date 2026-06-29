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
    public function metrics(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return new \Symfony\Component\HttpFoundation\Response("geo_proxy_up 1\n", 200, ["Content-Type" => "text/plain"]);
    }
}
