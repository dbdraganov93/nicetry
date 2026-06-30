<?php

declare(strict_types=1);

namespace GeoProxy\Model;

final readonly class RoutingRequest
{
    public function __construct(
        public string $clientId,
        public string $countryCode,
        public ?string $city = null,
        public ?string $targetHost = null,
        public ?string $sessionId = null,
        public int $requestedConnections = 1,
    ) {}

    public static function fromProxyUsername(string $proxyUsername, ?string $targetHost = null, ?string $sessionId = null): self
    {
        $parts = explode('.', $proxyUsername, 2);
        $countryCode = strtoupper($parts[0] ?? '');
        $clientId = $parts[1] ?? 'anonymous';

        return new self($clientId, $countryCode, null, $targetHost, $sessionId);
    }
}
