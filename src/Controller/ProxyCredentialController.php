<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProxyCredentialController
{
    public function create(Request $request): JsonResponse
    {
        $country = strtoupper((string) ($request->request->get('country') ?: 'DE'));
        $customer = preg_replace('/[^a-z0-9-]/', '', strtolower($request->headers->get('X-User-Id', 'demo-user')));

        return ApiResponse::json([
            'username' => strtolower($country) . '.' . $customer,
            'password' => 'px_' . bin2hex(random_bytes(18)),
            'country' => $country,
            'created_at' => gmdate(DATE_ATOM),
        ], 201);
    }
}
