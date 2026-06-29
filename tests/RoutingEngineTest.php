<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Entity\ExitNode;
use GeoProxy\Service\RoutingEngine;
use PHPUnit\Framework\TestCase;

final class RoutingEngineTest extends TestCase
{
    public function testSelectsLeastLoadedHealthyNodeForCountry(): void
    {
        $engine = new RoutingEngine();
        $node = $engine->selectNode('de.customer123', [
            new ExitNode('1', 'DE', 'Berlin', 'vpn-de-01', 'proxy-de-01', true, 10, 100),
            new ExitNode('2', 'DE', 'Berlin', 'vpn-de-02', 'proxy-de-02', true, 2, 100),
            new ExitNode('3', 'FR', 'Paris', 'vpn-fr-01', 'proxy-fr-01', true, 0, 100),
        ]);

        self::assertSame('2', $node->id);
    }
}
