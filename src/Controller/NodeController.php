<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NodeController
{
    public function register(Request $request): Response
    {
        return ApiResponse::json(['status' => 'registered'], 201);
    }
    public function heartbeat(Request $request): Response
    {
        return ApiResponse::json(['status' => 'healthy']);
    }
    public function publicIp(Request $request): Response
    {
        return ApiResponse::json(['public_ip_verified' => true]);
    }
}
