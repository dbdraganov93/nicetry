<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\UsageMonitor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class MonitoringController
{
    public function nodes(Request $request): JsonResponse
    {
        return ApiResponse::json(['nodes' => new UsageMonitor()->nodeHealth()]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $monitor = new UsageMonitor();

        return ApiResponse::json([
            'usage' => $monitor->currentPeriod($request->headers->get('X-User-Id', 'demo-user')),
            'nodes' => $monitor->nodeHealth(),
        ]);
    }
}
