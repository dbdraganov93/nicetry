<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Entity\ExitNode;
use GeoProxy\Model\ClientRoutingPolicy;
use GeoProxy\Model\RoutingRequest;
use GeoProxy\Service\Routing\InMemoryStickySessionStore;
use GeoProxy\Service\RoutingEngine;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RoutingEngineTest extends TestCase
{
    public function testSelectsLeastLoadedHealthyNodeForCountry(): void
    {
        $engine = new RoutingEngine();
        $node = $engine->selectNode('de.customer123', $this->nodes());

        self::assertSame('2', $node->id);
    }

    public function testEnforcesClientCountryTargetAndConcurrencyPolicy(): void
    {
        $engine = new RoutingEngine();
        $policy = new ClientRoutingPolicy('customer123', ['DE'], ['example.com'], 2);

        $node = $engine->selectRoute(
            new RoutingRequest('customer123', 'DE', targetHost: 'shop.example.com', requestedConnections: 1),
            $policy,
            $this->nodes(),
            ['customer123' => 1],
        );

        self::assertSame('2', $node->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exceeded max concurrency');
        $engine->selectRoute(
            new RoutingRequest('customer123', 'DE', targetHost: 'shop.example.com', requestedConnections: 2),
            $policy,
            $this->nodes(),
            ['customer123' => 1],
        );
    }

    public function testRejectsDisallowedCountry(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not allowed to use country FR');

        new RoutingEngine()->selectRoute(
            new RoutingRequest('customer123', 'FR', targetHost: 'example.com'),
            new ClientRoutingPolicy('customer123', ['DE'], ['example.com']),
            $this->nodes(),
        );
    }

    public function testStickySessionsReuseHealthyExitNode(): void
    {
        $store = new InMemoryStickySessionStore();
        $engine = new RoutingEngine($store);
        $policy = new ClientRoutingPolicy('customer123', ['DE']);
        $request = new RoutingRequest('customer123', 'DE', sessionId: 'browser-1');

        $first = $engine->selectRoute($request, $policy, $this->nodes());
        self::assertSame('2', $first->id);

        $second = $engine->selectRoute($request, $policy, [
            new ExitNode('1', 'DE', 'Berlin', 'vpn-de-01', 'proxy-de-01', true, 0, 100),
            new ExitNode('2', 'DE', 'Berlin', 'vpn-de-02', 'proxy-de-02', true, 20, 100),
        ]);

        self::assertSame('2', $second->id);
    }

    /** @return list<ExitNode> */
    private function nodes(): array
    {
        return [
            new ExitNode('1', 'DE', 'Berlin', 'vpn-de-01', 'proxy-de-01', true, 10, 100),
            new ExitNode('2', 'DE', 'Berlin', 'vpn-de-02', 'proxy-de-02', true, 2, 100),
            new ExitNode('3', 'FR', 'Paris', 'vpn-fr-01', 'proxy-fr-01', true, 0, 100),
        ];
    }
}
