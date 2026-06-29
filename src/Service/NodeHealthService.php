<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Entity\ExitNode;

final class NodeHealthService
{
    public function markHeartbeat(ExitNode $node, string $observedPublicIp): ExitNode
    {
        return $node->heartbeat($observedPublicIp);
    }

    public function isHealthy(ExitNode $node, int $maxHeartbeatAgeSeconds = 60): bool
    {
        $last = $node->getLastHeartbeatAt();
        return $node->healthy && ($last === null || (time() - $last->getTimestamp()) <= $maxHeartbeatAgeSeconds);
    }
}
