<?php

declare(strict_types=1);

namespace GeoProxy\Service\Routing;

interface StickySessionStoreInterface
{
    public function get(string $clientId, string $countryCode, string $sessionId): ?string;

    public function put(string $clientId, string $countryCode, string $sessionId, string $exitNodeId, int $ttlSeconds = 1800): void;
}
