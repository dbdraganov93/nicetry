<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CountryController
{
    public function list(Request $request): JsonResponse
    {
        return ApiResponse::json(['countries' => new FixtureRepository()->countries()]);
    }
}
