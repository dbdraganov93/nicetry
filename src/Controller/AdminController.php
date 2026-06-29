<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminController
{
    #[Route('/v1/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $fixtures = new FixtureRepository();

        return ApiResponse::json(['users' => count($fixtures->users()), 'nodes' => count($fixtures->nodes()), 'usage_bytes' => 11274289152, 'billing_mrr_cents' => 12800]);
    }
}
