<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Repository\FixtureRepository;

final class PlanCatalog
{
    public function __construct(private readonly FixtureRepository $repository = new FixtureRepository()) {}

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return array_map(static function (array $plan): array {
            $plan['monthly_bandwidth_limit_bytes'] = is_int($plan['monthly_bandwidth_limit_gb'])
                ? $plan['monthly_bandwidth_limit_gb'] * 1024 * 1024 * 1024
                : null;

            return $plan;
        }, $this->repository->plans());
    }

    /** @return array<string, mixed>|null */
    public function find(string $code): ?array
    {
        foreach ($this->all() as $plan) {
            if ($plan['code'] === $code) {
                return $plan;
            }
        }

        return null;
    }
}
