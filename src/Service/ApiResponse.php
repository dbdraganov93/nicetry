<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponse
{
    /** @param array<string, mixed> $payload */
    public static function json(array $payload, int $status = 200): JsonResponse
    {
        return new JsonResponse($payload, $status, ['X-Content-Type-Options' => 'nosniff']);
    }
}
