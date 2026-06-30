<?php

declare(strict_types=1);

namespace NiceTry\Sdk;

use RuntimeException;

final class NiceTry
{
    /** @param callable(string, array<string, mixed>): array{status:int,body:string} $transport */
    public function __construct(
        private readonly string $baseUrl = 'http://localhost:8080',
        private readonly ?string $apiKey = null,
        private readonly mixed $transport = null,
    ) {}

    public function request(string $url, string $country, int $timeoutSeconds = 45): string
    {
        $response = $this->send($url, $country, $timeoutSeconds, false);

        return (string) $response['body'];
    }

    /** @return array<string, mixed> */
    public function requestEnvelope(string $url, string $country, int $timeoutSeconds = 45): array
    {
        $response = $this->send($url, $country, $timeoutSeconds, true);
        $decoded = json_decode((string) $response['body'], true);
        if (!is_array($decoded)) {
            throw new RuntimeException('nicetry_invalid_json_response');
        }

        return $decoded;
    }

    /** @return array{status:int,body:string} */
    private function send(string $url, string $country, int $timeoutSeconds, bool $envelope): array
    {
        $payload = [
            'url' => $this->normalizeUrl($url),
            'country' => strtoupper(trim($country)),
            'timeout_seconds' => $timeoutSeconds,
            'response' => $envelope ? 'envelope' : 'raw',
        ];
        $headers = ['Content-Type: application/json'];
        if ($this->apiKey !== null && $this->apiKey !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        $transport = $this->transport;
        $response = is_callable($transport)
            ? $transport($this->endpoint(), ['headers' => $headers, 'payload' => $payload, 'timeout_seconds' => $timeoutSeconds])
            : $this->curl($this->endpoint(), $headers, $payload, $timeoutSeconds);

        if ($response['status'] < 200 || $response['status'] >= 300) {
            throw new RuntimeException('nicetry_request_failed: HTTP ' . $response['status'] . ' ' . $response['body']);
        }

        return $response;
    }

    private function endpoint(): string
    {
        return rtrim($this->baseUrl, '/') . '/v1/fetch';
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if (!str_contains($url, '://')) {
            return 'https://' . $url;
        }

        return $url;
    }

    /** @param list<string> $headers @param array<string, mixed> $payload @return array{status:int,body:string} */
    private function curl(string $endpoint, array $headers, array $payload, int $timeoutSeconds): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('nicetry_requires_ext_curl');
        }

        $handle = curl_init($endpoint);
        if ($handle === false) {
            throw new RuntimeException('nicetry_curl_init_failed');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => max(5, min(120, $timeoutSeconds + 5)),
        ]);

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($body === false) {
            throw new RuntimeException('nicetry_curl_failed: ' . $error);
        }

        return ['status' => $status, 'body' => (string) $body];
    }
}
