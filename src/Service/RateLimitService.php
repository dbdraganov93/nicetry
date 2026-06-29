<?php

declare(strict_types=1);

namespace GeoProxy\Service;

final class RateLimitService
{
    /** @var array<string, array{count:int, reset:int}> */
    private array $buckets = [];

    public function allow(string $key, int $limit = 120, int $windowSeconds = 60): bool
    {
        $now = time();
        $bucket = $this->buckets[$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];
        if ($bucket['reset'] <= $now) {
            $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
        }
        $bucket['count']++;
        $this->buckets[$key] = $bucket;
        return $bucket['count'] <= $limit;
    }
}
