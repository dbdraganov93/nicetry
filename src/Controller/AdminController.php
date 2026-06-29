<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminController
{
    public function dashboard(Request $request): Response
    {
        return ApiResponse::json(['users' => 0, 'nodes' => 0, 'usage_bytes' => 0, 'billing_mrr_cents' => 0]);
    }
}
