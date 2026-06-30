<?php

declare(strict_types=1);

namespace GeoProxy\Repository;

final class FixtureRepository
{
    /** @var array<string, mixed> */
    private array $data;

    public function __construct(?string $fixturePath = null)
    {
        $this->data = require $fixturePath ?? dirname(__DIR__, 2) . '/config/fixtures/platform.php';
    }

    /** @return list<array<string, mixed>> */
    public function plans(): array
    {
        return $this->data['plans'];
    }

    /** @return list<array<string, mixed>> */
    public function countries(): array
    {
        return $this->data['countries'];
    }

    /** @return list<array<string, mixed>> */
    public function nodes(): array
    {
        return $this->data['nodes'];
    }

    /** @return list<array<string, mixed>> */
    public function users(): array
    {
        return $this->data['users'];
    }



    /** @return list<array<string, mixed>> */
    public function routingPolicies(): array
    {
        return $this->data['routing_policies'] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function exitPools(): array
    {
        return $this->data['exit_pools'] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function targetPolicies(): array
    {
        return $this->data['target_policies'] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function scrapeJobs(): array
    {
        return $this->data['scrape_jobs'] ?? [];
    }

    /** @return array<string, mixed>|null */
    public function routingPolicyFor(string $clientId): ?array
    {
        foreach ($this->routingPolicies() as $policy) {
            if ((string) $policy['client_id'] === $clientId) {
                return $policy;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function apiKeysFor(string $userId): array
    {
        return $this->data['api_keys'][$userId] ?? [];
    }

    /** @return array<string, mixed>|null */
    public function userById(string $userId): ?array
    {
        foreach ($this->users() as $user) {
            if ((string) $user['id'] === $userId) {
                return $user;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    public function userByEmail(string $email): ?array
    {
        foreach ($this->users() as $user) {
            if (strtolower((string) $user['email']) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function usageFor(string $userId): array
    {
        return $this->data['usage'][$userId] ?? ['plan' => 'free', 'period' => gmdate('Y-m'), 'requests' => 0, 'bytes_in' => 0, 'bytes_out' => 0, 'connection_seconds' => 0, 'countries' => [], 'errors' => 0, 'average_latency_ms' => null];
    }
}
