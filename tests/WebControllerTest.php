<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Controller\WebController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class WebControllerTest extends TestCase
{
    public function testAdminPanelRequiresLoginBeforeShowingControls(): void
    {
        $html = new WebController()->admin(Request::create('/admin'))->getContent();

        self::assertIsString($html);
        self::assertStringContainsString('Admin sign in required', $html);
        self::assertStringNotContainsString('Operations dashboard', $html);
        self::assertStringNotContainsString('User control', $html);
    }

    public function testAdminPanelShowsUserAndVpnControlsAfterLogin(): void
    {
        $request = Request::create('/admin');
        $request->cookies->set('admin_session', hash_hmac('sha256', 'admin@geoproxy.test', 'dev-secret'));
        $html = new WebController()->admin($request)->getContent();

        self::assertIsString($html);
        self::assertStringContainsString('Operations dashboard', $html);
        self::assertStringContainsString('User control', $html);
        self::assertStringContainsString('NordVPN CLI configuration', $html);
        self::assertStringContainsString('NordVPN location', $html);
        self::assertStringContainsString('admin@geoproxy.test', $html);
    }

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
