<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Model\ClientRoutingPolicy;
use GeoProxy\Model\RoutingRequest;
use GeoProxy\Service\Queue\ScrapeQueuePlanner;
use PHPUnit\Framework\TestCase;

final class ScrapeQueuePlannerTest extends TestCase
{
    public function testBuildsSharedCountryTargetQueueName(): void
    {
        $queue = new ScrapeQueuePlanner()->queueName(
            new RoutingRequest('client-a', 'de', targetHost: 'shop.example.com'),
            new ClientRoutingPolicy('client-a', priority: 100),
            'retail',
        );

        self::assertSame('scrape.normal.DE.retail.shop-example-com', $queue);
    }

    public function testBuildsDedicatedPoolQueueNameForEnterprisePolicy(): void
    {
        $queue = new ScrapeQueuePlanner()->queueName(
            new RoutingRequest('enterprise', 'us', targetHost: 'example.com'),
            new ClientRoutingPolicy('enterprise', dedicatedPoolId: 'pool-enterprise-us', priority: 25),
        );

        self::assertSame('scrape.high.US.pool-enterprise-us.example-com', $queue);
    }
}
