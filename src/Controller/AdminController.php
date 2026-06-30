<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminController
{
    #[Route('/v1/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $fixtures = new FixtureRepository();

        return ApiResponse::json(['users' => count($fixtures->users()), 'nodes' => count($fixtures->nodes()), 'usage_bytes' => 11274289152, 'billing_mrr_cents' => 12800]);
    }

    #[Route('/v1/admin/users', name: 'admin_users', methods: ['GET'])]
    public function users(): Response
    {
        $users = array_map(static fn(array $user): array => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'roles' => $user['roles'],
            'plan' => $user['plan'],
            'status' => $user['status'],
        ], new FixtureRepository()->users());

        return ApiResponse::json(['users' => $users]);
    }

    #[Route('/v1/admin/vpn/settings', name: 'admin_vpn_settings', methods: ['GET', 'POST'])]
    public function vpnSettings(Request $request): Response
    {
        $payload = $request->request->all();

        return ApiResponse::json([
            'status' => $request->isMethod('POST') ? 'saved' : 'current',
            'settings' => [
                'auth_mode' => (string) ($payload['auth_mode'] ?? 'wireguard'),
                'token_rotation' => (string) ($payload['token_rotation'] ?? '30 days'),
                'allowed_cidrs' => (string) ($payload['allowed_cidrs'] ?? '10.8.0.0/24, fd42:42:42::/64'),
                'tokens' => array_map(static fn(array $node): array => [
                    'node_id' => $node['id'],
                    'token_name' => 'wg-' . $node['id'] . '-token',
                    'status' => $node['healthy'] ? 'active' : 'disabled',
                ], new FixtureRepository()->nodes()),
            ],
        ]);
    }
}
