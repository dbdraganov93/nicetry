<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Entity\ExitNode;
use RuntimeException;

final class RoutingEngine
{
    /**
     * @param list<ExitNode> $nodes
     */
    public function selectNode(string $proxyUsername, array $nodes): ExitNode
    {
        $countryCode = strtoupper(strtok($proxyUsername, '.') ?: '');
        $eligible = array_values(array_filter(
            $nodes,
            static fn(ExitNode $node): bool => $node->healthy && $node->countryCode === $countryCode
        ));

        usort($eligible, static fn(ExitNode $a, ExitNode $b): int => [$a->activeConnections, -$a->weight] <=> [$b->activeConnections, -$b->weight]);

        return $eligible[0] ?? throw new RuntimeException(sprintf('No healthy exit nodes for %s', $countryCode));
    }
}
