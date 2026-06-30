<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Controller\RoutingPolicyController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoutingPolicyControllerTest extends TestCase
{
    public function testListsRoutingControlPlaneResources(): void
    {
        $controller = new RoutingPolicyController();

        self::assertArrayHasKey('routing_policies', $this->decode($controller->policies()));
        self::assertArrayHasKey('exit_pools', $this->decode($controller->exitPools()));
        self::assertArrayHasKey('target_policies', $this->decode($controller->targetPolicies()));
        self::assertArrayHasKey('scrape_jobs', $this->decode($controller->scrapeJobs()));
    }

    public function testPlansAllowedScrapeIntoPartitionedQueue(): void
    {
        $response = new RoutingPolicyController()->plan(Request::create('/v1/scrape/plan', 'POST', [], [], [], [], json_encode([
            'client_id' => 'demo-user',
            'url' => 'https://example.com/catalog.json',
            'country_code' => 'DE',
            'session_id' => 'browser-1',
        ], JSON_THROW_ON_ERROR)));
        $payload = $this->decode($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('scrape.normal.DE.normal.example-com', $payload['queue']);
        self::assertTrue($payload['sticky_sessions_enabled']);
    }

    public function testRejectsScrapePlanWhenTargetIsNotAllowed(): void
    {
        $response = new RoutingPolicyController()->plan(Request::create('/v1/scrape/plan', 'POST', [], [], [], [], json_encode([
            'client_id' => 'demo-user',
            'url' => 'https://blocked.example.net/private',
            'country_code' => 'DE',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertSame('target_not_allowed', $this->decode($response)['error']);
    }

    /** @return array<string, mixed> */
    private function decode(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
