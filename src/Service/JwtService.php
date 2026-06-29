<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use RuntimeException;

final class JwtService
{
    public function issue(string $subject, array $roles, string $secret, int $ttlSeconds = 3600): string
    {
        $now = time();
        $payload = ['sub' => $subject, 'roles' => $roles, 'iat' => $now, 'exp' => $now + $ttlSeconds];
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $body, $secret, true));
        return $header . '.' . $body . '.' . $signature;
    }

    public function verify(string $token, string $secret): array
    {
        [$header, $body, $signature] = array_pad(explode('.', $token), 3, '');
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $body, $secret, true));
        if (!hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid JWT signature.');
        }
        $payload = json_decode(base64_decode(strtr($body, '-_', '+/')) ?: '', true, 512, JSON_THROW_ON_ERROR);
        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Expired JWT.');
        }
        return $payload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
