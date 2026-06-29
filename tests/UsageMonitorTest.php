<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Service\UsageMonitor;
use PHPUnit\Framework\TestCase;

final class UsageMonitorTest extends TestCase
{
    public function testUsageIncludesPlanLimitsAndPercentUsed(): void
    {
        $usage = new UsageMonitor()->currentPeriod('demo-user');

        self::assertSame('starter', $usage['plan']);
        self::assertSame(250000, $usage['limits']['requests']);
        self::assertGreaterThan(0, $usage['percent_used']['requests']);
        self::assertArrayHasKey('DE', $usage['countries']);
    }
}
