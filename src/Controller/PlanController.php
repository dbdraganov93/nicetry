<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\PlanCatalog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PlanController
{
    #[\Symfony\Component\Routing\Attribute\Route('/v1/plans', name: 'plans_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return ApiResponse::json(['plans' => new PlanCatalog()->all()]);
    }
}
