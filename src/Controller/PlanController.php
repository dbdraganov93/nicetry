<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\PlanCatalog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PlanController
{
    public function list(Request $request): JsonResponse
    {
        return ApiResponse::json(['plans' => new PlanCatalog()->all()]);
    }
}
