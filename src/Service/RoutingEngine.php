<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Entity\ExitNode;
use GeoProxy\Model\ClientRoutingPolicy;
use GeoProxy\Model\RoutingRequest;
use GeoProxy\Service\Routing\StickySessionStoreInterface;
use RuntimeException;

final class RoutingEngine
{
    public function __construct(private readonly ?StickySessionStoreInterface $stickySessionStore = null) {}

    /**
     * @param list<ExitNode> $nodes
     */
    public function selectNode(string $proxyUsername, array $nodes): ExitNode
    {
        return $this->selectRoute(
            RoutingRequest::fromProxyUsername($proxyUsername),
            new ClientRoutingPolicy(explode('.', $proxyUsername, 2)[1] ?? 'anonymous'),
            $nodes,
            [],
        );
    }

    /**
     * @param list<ExitNode> $nodes
     * @param array<string, int> $clientActiveConnections
     */
    public function selectRoute(
        RoutingRequest $request,
        ClientRoutingPolicy $policy,
        array $nodes,
        array $clientActiveConnections = [],
    ): ExitNode {
        $countryCode = strtoupper($request->countryCode);
        $this->assertPolicyAllowsRequest($request, $policy, $clientActiveConnections);

        $eligible = array_values(array_filter(
            $nodes,
            static fn(ExitNode $node): bool => $node->healthy && $node->countryCode === $countryCode
        ));

        if ($request->city !== null) {
            $eligible = array_values(array_filter(
                $eligible,
                static fn(ExitNode $node): bool => $node->city !== null && strcasecmp($node->city, $request->city) === 0,
            ));
        }

        if ($policy->stickySessionsEnabled && $request->sessionId !== null && $this->stickySessionStore !== null) {
            $stickyNodeId = $this->stickySessionStore->get($policy->clientId, $countryCode, $request->sessionId);
            if ($stickyNodeId !== null) {
                foreach ($eligible as $node) {
                    if ($node->id === $stickyNodeId) {
                        return $node;
                    }
                }
            }
        }

        usort($eligible, static fn(ExitNode $a, ExitNode $b): int => [
            $a->activeConnections,
            -$a->weight,
            $a->id,
        ] <=> [
            $b->activeConnections,
            -$b->weight,
            $b->id,
        ]);

        $selected = $eligible[0] ?? throw new RuntimeException(sprintf('No healthy exit nodes for %s', $countryCode));

        if ($policy->stickySessionsEnabled && $request->sessionId !== null && $this->stickySessionStore !== null) {
            $this->stickySessionStore->put($policy->clientId, $countryCode, $request->sessionId, $selected->id);
        }

        return $selected;
    }

    /** @param array<string, int> $clientActiveConnections */
    private function assertPolicyAllowsRequest(RoutingRequest $request, ClientRoutingPolicy $policy, array $clientActiveConnections): void
    {
        if (!$policy->allowsCountry($request->countryCode)) {
            throw new RuntimeException(sprintf('Client %s is not allowed to use country %s', $policy->clientId, strtoupper($request->countryCode)));
        }

        if (!$policy->allowsTarget($request->targetHost)) {
            throw new RuntimeException(sprintf('Client %s is not allowed to scrape %s', $policy->clientId, $request->targetHost ?? 'unknown target'));
        }

        $activeConnections = $clientActiveConnections[$policy->clientId] ?? 0;
        if ($activeConnections + $request->requestedConnections > $policy->maxConcurrentRequests) {
            throw new RuntimeException(sprintf('Client %s exceeded max concurrency of %d', $policy->clientId, $policy->maxConcurrentRequests));
        }
    }
}
