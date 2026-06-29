<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ApiKeyController
{
    #[Route('/v1/api-keys', name: 'api_keys_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $allowedIps = $this->ipWhitelist($request);

        return ApiResponse::json([
            'id' => bin2hex(random_bytes(16)),
            'prefix' => 'gp_' . bin2hex(random_bytes(4)),
            'secret' => 'gp_live_' . bin2hex(random_bytes(24)),
            'auth_methods' => ['Authorization: Bearer', 'X-API-Key'],
            'ip_whitelist' => $allowedIps,
            'created_at' => gmdate(DATE_ATOM),
        ], 201);
    }

    #[Route('/v1/api-keys/{id}/rotate', name: 'api_keys_rotate', requirements: ['id' => '[A-Za-z0-9_-]+'], methods: ['POST'])]
    public function rotate(string $id): JsonResponse
    {
        return ApiResponse::json([
            'id' => $id,
            'prefix' => 'gp_' . bin2hex(random_bytes(4)),
            'secret' => 'gp_live_' . bin2hex(random_bytes(24)),
            'rotated_at' => gmdate(DATE_ATOM),
            'previous_token_status' => 'revoked',
        ]);
    }

    #[Route('/v1/api-keys/{id}/ip-whitelist', name: 'api_keys_ip_whitelist', requirements: ['id' => '[A-Za-z0-9_-]+'], methods: ['POST', 'PUT'])]
    public function updateIpWhitelist(Request $request, string $id): JsonResponse
    {
        $allowedIps = $this->ipWhitelist($request);
        foreach ($allowedIps as $ip) {
            if (!$this->isValidIpOrCidr($ip)) {
                return ApiResponse::json(['error' => 'invalid_ip_whitelist', 'ip' => $ip], 422);
            }
        }

        return ApiResponse::json([
            'id' => $id,
            'ip_whitelist' => $allowedIps,
            'updated_at' => gmdate(DATE_ATOM),
        ]);
    }

    #[Route('/v1/api-keys/{id}', name: 'api_keys_delete', requirements: ['id' => '[A-Za-z0-9_-]+'], methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        return ApiResponse::json(['deleted' => true]);
    }

    /** @return list<string> */
    private function ipWhitelist(Request $request): array
    {
        $payload = $request->getContentTypeFormat() === 'json' ? json_decode($request->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR) : $request->request->all();
        $value = $payload['ip_whitelist'] ?? [];
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        return array_values(array_filter(is_array($value) ? $value : [], static fn(mixed $ip): bool => is_string($ip) && $ip !== ''));
    }

    private function isValidIpOrCidr(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        [$ip, $prefix] = array_pad(explode('/', $value, 2), 2, null);
        if ($prefix === null || filter_var($ip, FILTER_VALIDATE_IP) === false || !ctype_digit($prefix)) {
            return false;
        }

        $max = str_contains($ip, ':') ? 128 : 32;
        return (int) $prefix >= 0 && (int) $prefix <= $max;
    }
}
