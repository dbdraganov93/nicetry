<?php

declare(strict_types=1);

namespace GeoProxy\Service\Queue;

use GeoProxy\Model\ClientRoutingPolicy;
use GeoProxy\Model\RoutingRequest;

final class ScrapeQueuePlanner
{
    public function queueName(RoutingRequest $request, ClientRoutingPolicy $policy, string $riskLevel = 'normal'): string
    {
        $priority = $policy->priority <= 50 ? 'high' : ($policy->priority >= 200 ? 'low' : 'normal');
        $target = $request->targetHost === null ? 'generic' : preg_replace('/[^a-z0-9]+/', '-', strtolower($request->targetHost));
        $target = trim((string) $target, '-') ?: 'generic';

        if ($policy->dedicatedPoolId !== null) {
            return sprintf('scrape.%s.%s.%s.%s', $priority, strtoupper($request->countryCode), $policy->dedicatedPoolId, $target);
        }

        return sprintf('scrape.%s.%s.%s.%s', $priority, strtoupper($request->countryCode), $riskLevel, $target);
    }
}
