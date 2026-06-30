<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Model\ClientRoutingPolicy;
use GeoProxy\Model\RoutingRequest;
use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\Queue\ScrapeQueuePlanner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RoutingPolicyController
{
    public function __construct(
        private readonly FixtureRepository $fixtures = new FixtureRepository(),
        private readonly ScrapeQueuePlanner $queuePlanner = new ScrapeQueuePlanner(),
    ) {}

    #[Route('/v1/routing/policies', name: 'routing_policies_index', methods: ['GET'])]
    public function policies(): Response
    {
        return ApiResponse::json(['routing_policies' => $this->fixtures->routingPolicies()]);
    }

    #[Route('/v1/routing/exit-pools', name: 'routing_exit_pools_index', methods: ['GET'])]
    public function exitPools(): Response
    {
        return ApiResponse::json(['exit_pools' => $this->fixtures->exitPools()]);
    }

    #[Route('/v1/routing/target-policies', name: 'routing_target_policies_index', methods: ['GET'])]
    public function targetPolicies(): Response
    {
        return ApiResponse::json(['target_policies' => $this->fixtures->targetPolicies()]);
    }

    #[Route('/v1/scrape/jobs', name: 'scrape_jobs_index', methods: ['GET'])]
    public function scrapeJobs(): Response
    {
        return ApiResponse::json(['scrape_jobs' => $this->fixtures->scrapeJobs()]);
    }

    #[Route('/v1/scrape/plan', name: 'scrape_plan', methods: ['POST'])]
    public function plan(Request $request): Response
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return ApiResponse::json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        $clientId = (string) ($payload['client_id'] ?? 'demo-user');
        $policyData = $this->fixtures->routingPolicyFor($clientId);
        if ($policyData === null) {
            return ApiResponse::json(['error' => 'unknown_client_policy'], Response::HTTP_NOT_FOUND);
        }

        $url = (string) ($payload['url'] ?? '');
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return ApiResponse::json(['error' => 'invalid_url'], Response::HTTP_BAD_REQUEST);
        }

        $policy = new ClientRoutingPolicy(
            $clientId,
            $policyData['allowed_countries'],
            $policyData['allowed_targets'],
            (int) $policyData['max_concurrent_requests'],
            (bool) $policyData['sticky_sessions_enabled'],
            $policyData['dedicated_pool_id'],
            (int) $policyData['priority'],
        );
        $requestModel = new RoutingRequest(
            $clientId,
            (string) ($payload['country_code'] ?? 'DE'),
            $payload['city'] ?? null,
            $host,
            $payload['session_id'] ?? null,
        );

        if (!$policy->allowsCountry($requestModel->countryCode)) {
            return ApiResponse::json(['error' => 'country_not_allowed'], Response::HTTP_FORBIDDEN);
        }

        if (!$policy->allowsTarget($requestModel->targetHost)) {
            return ApiResponse::json(['error' => 'target_not_allowed'], Response::HTTP_FORBIDDEN);
        }

        $targetPolicy = $this->targetPolicyFor($host);
        $queue = $this->queuePlanner->queueName($requestModel, $policy, (string) ($targetPolicy['risk_level'] ?? 'normal'));

        return ApiResponse::json([
            'client_id' => $clientId,
            'country_code' => strtoupper($requestModel->countryCode),
            'target_host' => $host,
            'sticky_sessions_enabled' => $policy->stickySessionsEnabled,
            'dedicated_pool_id' => $policy->dedicatedPoolId,
            'queue' => $queue,
            'target_policy' => $targetPolicy,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function targetPolicyFor(string $host): ?array
    {
        foreach ($this->fixtures->targetPolicies() as $policy) {
            $domain = strtolower((string) $policy['domain']);
            $normalizedHost = strtolower($host);
            if ($normalizedHost === $domain || str_ends_with($normalizedHost, '.' . $domain)) {
                return $policy;
            }
        }

        return null;
    }
}
