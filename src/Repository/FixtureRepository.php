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

    /** @return array<string, mixed> */
    public function usageFor(string $userId): array
    {
        return $this->data['usage'][$userId] ?? ['plan' => 'free', 'period' => gmdate('Y-m'), 'requests' => 0, 'bytes_in' => 0, 'bytes_out' => 0, 'connection_seconds' => 0, 'countries' => [], 'errors' => 0, 'average_latency_ms' => null];
    }
}
