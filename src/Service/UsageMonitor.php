<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Repository\FixtureRepository;

final class UsageMonitor
{
    public function __construct(
        private readonly FixtureRepository $repository = new FixtureRepository(),
        private readonly PlanCatalog $plans = new PlanCatalog(),
    ) {}

    /** @return array<string, mixed> */
    public function currentPeriod(string $userId): array
    {
        $usage = $this->repository->usageFor($userId);
        $plan = $this->plans->find((string) $usage['plan']) ?? $this->plans->find('free');
        $bytes = (int) $usage['bytes_in'] + (int) $usage['bytes_out'];
        $requestLimit = $plan['monthly_request_limit'] ?? null;
        $bandwidthLimit = $plan['monthly_bandwidth_limit_bytes'] ?? null;

        return $usage + [
            'total_bytes' => $bytes,
            'limits' => [
                'requests' => $requestLimit,
                'bandwidth_bytes' => $bandwidthLimit,
                'concurrent_connections' => $plan['concurrent_connections'] ?? null,
            ],
            'percent_used' => [
                'requests' => is_int($requestLimit) ? round(((int) $usage['requests'] / $requestLimit) * 100, 2) : null,
                'bandwidth' => is_int($bandwidthLimit) ? round(($bytes / $bandwidthLimit) * 100, 2) : null,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function nodeHealth(): array
    {
        return array_map(static function (array $node): array {
            $node['load_percent'] = round(((int) $node['active_connections'] / (int) $node['capacity']) * 100, 2);
            $node['status'] = $node['healthy'] ? 'available' : 'disabled';

            return $node;
        }, $this->repository->nodes());
    }
}
