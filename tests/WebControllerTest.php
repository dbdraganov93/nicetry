<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Controller\WebController;
use PHPUnit\Framework\TestCase;

final class WebControllerTest extends TestCase
{
    public function testDashboardShowsUsageRotationAuthAndWhitelist(): void
    {
        $html = new WebController()->dashboard()->getContent();

        self::assertIsString($html);
        self::assertStringContainsString('Requests this period', $html);
        self::assertStringContainsString('Rotate token', $html);
        self::assertStringContainsString('Authorization: Bearer gp_', $html);
        self::assertStringContainsString('X-API-Key: gp_', $html);
        self::assertStringContainsString('Allowed source IPs', $html);
        self::assertStringContainsString('198.51.100.24', $html);
    }
}
