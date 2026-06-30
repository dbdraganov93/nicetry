<?php

declare(strict_types=1);

namespace GeoProxy\Service\Routing;

final class InMemoryStickySessionStore implements StickySessionStoreInterface
{
    /** @var array<string, array{node: string, expires_at: int}> */
    private array $sessions = [];

    public function get(string $clientId, string $countryCode, string $sessionId): ?string
    {
        $key = $this->key($clientId, $countryCode, $sessionId);
        $record = $this->sessions[$key] ?? null;
        if ($record === null) {
            return null;
        }

        if ($record['expires_at'] < time()) {
            unset($this->sessions[$key]);
            return null;
        }

        return $record['node'];
    }

    public function put(string $clientId, string $countryCode, string $sessionId, string $exitNodeId, int $ttlSeconds = 1800): void
    {
        $this->sessions[$this->key($clientId, $countryCode, $sessionId)] = [
            'node' => $exitNodeId,
            'expires_at' => time() + $ttlSeconds,
        ];
    }

    private function key(string $clientId, string $countryCode, string $sessionId): string
    {
        return sprintf('sticky:%s:%s:%s', $clientId, strtoupper($countryCode), $sessionId);
    }
}
