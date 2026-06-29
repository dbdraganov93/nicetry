<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminController
{
    #[Route('/v1/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        return ApiResponse::json(['users' => 0, 'nodes' => 0, 'usage_bytes' => 0, 'billing_mrr_cents' => 0]);
    }
}
