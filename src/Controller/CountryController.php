<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CountryController
{
    public function list(Request $request): JsonResponse
    {
        return ApiResponse::json([
            ['country' => 'Germany', 'code' => 'DE', 'cities' => ['Berlin', 'Hamburg']],
            ['country' => 'France', 'code' => 'FR', 'cities' => ['Paris', 'Marseille']],
            ['country' => 'Netherlands', 'code' => 'NL', 'cities' => ['Amsterdam']],
            ['country' => 'United States', 'code' => 'US', 'cities' => ['New York', 'Los Angeles']],
            ['country' => 'Italy', 'code' => 'IT', 'cities' => ['Milan', 'Rome']],
            ['country' => 'Spain', 'code' => 'ES', 'cities' => ['Madrid', 'Barcelona']],
        ]);
    }
}
