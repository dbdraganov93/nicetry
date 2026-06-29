<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NodeController
{
    #[Route('/v1/nodes/register', name: 'nodes_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        return ApiResponse::json(['status' => 'registered'], 201);
    }
    #[Route('/v1/nodes/heartbeat', name: 'nodes_heartbeat', methods: ['POST'])]
    public function heartbeat(Request $request): Response
    {
        return ApiResponse::json(['status' => 'healthy']);
    }
    #[Route('/v1/nodes/public-ip', name: 'nodes_public_ip', methods: ['POST'])]
    public function publicIp(Request $request): Response
    {
        return ApiResponse::json(['public_ip_verified' => true]);
    }
}
