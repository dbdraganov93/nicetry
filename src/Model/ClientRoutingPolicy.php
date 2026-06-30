<?php

declare(strict_types=1);

namespace GeoProxy\Model;

use InvalidArgumentException;

final readonly class ClientRoutingPolicy
{
    /**
     * @param list<string> $allowedCountries
     * @param list<string> $allowedTargets
     */
    public function __construct(
        public string $clientId,
        public array $allowedCountries = [],
        public array $allowedTargets = [],
        public int $maxConcurrentRequests = 25,
        public bool $stickySessionsEnabled = true,
        public ?string $dedicatedPoolId = null,
        public int $priority = 100,
    ) {
        if ($maxConcurrentRequests < 1) {
            throw new InvalidArgumentException('maxConcurrentRequests must be at least 1.');
        }
    }

    public function allowsCountry(string $countryCode): bool
    {
        return $this->allowedCountries === [] || in_array(strtoupper($countryCode), array_map('strtoupper', $this->allowedCountries), true);
    }

    public function allowsTarget(?string $targetHost): bool
    {
        if ($targetHost === null || $this->allowedTargets === []) {
            return true;
        }

        $normalizedHost = strtolower($targetHost);
        foreach ($this->allowedTargets as $allowedTarget) {
            $allowedTarget = strtolower($allowedTarget);
            if ($normalizedHost === $allowedTarget || str_ends_with($normalizedHost, '.' . $allowedTarget)) {
                return true;
            }
        }

        return false;
    }
}
