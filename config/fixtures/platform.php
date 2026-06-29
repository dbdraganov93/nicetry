<?php

declare(strict_types=1);

return [
    'plans' => [
        ['code' => 'free', 'name' => 'Free', 'price_cents' => 0, 'monthly_request_limit' => 10000, 'monthly_bandwidth_limit_gb' => 1, 'concurrent_connections' => 5, 'countries' => ['DE'], 'features' => ['community_support']],
        ['code' => 'starter', 'name' => 'Starter', 'price_cents' => 2900, 'monthly_request_limit' => 250000, 'monthly_bandwidth_limit_gb' => 50, 'concurrent_connections' => 50, 'countries' => ['DE', 'FR', 'NL', 'US'], 'features' => ['email_support', 'api_keys']],
        ['code' => 'pro', 'name' => 'Pro', 'price_cents' => 9900, 'monthly_request_limit' => 2000000, 'monthly_bandwidth_limit_gb' => 500, 'concurrent_connections' => 500, 'countries' => ['DE', 'FR', 'NL', 'US', 'IT', 'ES'], 'features' => ['priority_support', 'sticky_sessions', 'city_routing']],
        ['code' => 'enterprise', 'name' => 'Enterprise', 'price_cents' => null, 'monthly_request_limit' => null, 'monthly_bandwidth_limit_gb' => null, 'concurrent_connections' => null, 'countries' => ['DE', 'FR', 'NL', 'US', 'IT', 'ES'], 'features' => ['dedicated_ips', 'sso', 'custom_slas', 'account_manager']],
    ],
    'countries' => [
        ['country' => 'Germany', 'code' => 'DE', 'cities' => ['Berlin', 'Hamburg'], 'current_ips' => ['203.0.113.10', '203.0.113.11']],
        ['country' => 'France', 'code' => 'FR', 'cities' => ['Paris', 'Marseille'], 'current_ips' => ['203.0.113.20']],
        ['country' => 'Netherlands', 'code' => 'NL', 'cities' => ['Amsterdam'], 'current_ips' => ['203.0.113.30']],
        ['country' => 'United States', 'code' => 'US', 'cities' => ['New York', 'Los Angeles'], 'current_ips' => ['203.0.113.40', '203.0.113.41']],
        ['country' => 'Italy', 'code' => 'IT', 'cities' => ['Milan', 'Rome'], 'current_ips' => ['203.0.113.50']],
        ['country' => 'Spain', 'code' => 'ES', 'cities' => ['Madrid', 'Barcelona'], 'current_ips' => ['203.0.113.60']],
    ],
    'nodes' => [
        ['id' => 'de-01', 'country_code' => 'DE', 'city' => 'Berlin', 'healthy' => true, 'active_connections' => 12, 'capacity' => 500, 'latency_ms' => 43, 'public_ip' => '203.0.113.10', 'vpn_uptime_seconds' => 86400],
        ['id' => 'de-02', 'country_code' => 'DE', 'city' => 'Hamburg', 'healthy' => true, 'active_connections' => 8, 'capacity' => 500, 'latency_ms' => 39, 'public_ip' => '203.0.113.11', 'vpn_uptime_seconds' => 43200],
        ['id' => 'fr-01', 'country_code' => 'FR', 'city' => 'Paris', 'healthy' => true, 'active_connections' => 19, 'capacity' => 500, 'latency_ms' => 51, 'public_ip' => '203.0.113.20', 'vpn_uptime_seconds' => 81000],
        ['id' => 'nl-01', 'country_code' => 'NL', 'city' => 'Amsterdam', 'healthy' => true, 'active_connections' => 7, 'capacity' => 500, 'latency_ms' => 45, 'public_ip' => '203.0.113.30', 'vpn_uptime_seconds' => 71200],
        ['id' => 'us-01', 'country_code' => 'US', 'city' => 'New York', 'healthy' => true, 'active_connections' => 22, 'capacity' => 1000, 'latency_ms' => 92, 'public_ip' => '203.0.113.40', 'vpn_uptime_seconds' => 65000],
    ],

    'users' => [
        ['id' => 'admin-user', 'name' => 'Alex Morgan', 'email' => 'admin@geoproxy.test', 'password_hash' => '$2y$12$fEutvlIO0.7IQyim94RaUOT7CzMHaONe.B8UTH51gb6r.HxDZE35y', 'roles' => ['ROLE_ADMIN', 'ROLE_USER'], 'plan' => 'enterprise', 'status' => 'active'],
        ['id' => 'demo-user', 'name' => 'Jamie Carter', 'email' => 'user@geoproxy.test', 'password_hash' => '$2y$12$4kFqHjmhXb88/DVh9ggoIOxCdT5UuudOyudRVcVnAvVJH0sSGKM5S', 'roles' => ['ROLE_USER'], 'plan' => 'starter', 'status' => 'active'],
        ['id' => 'maya-user', 'name' => 'Maya Chen', 'email' => 'maya@geoproxy.test', 'password_hash' => '$2y$12$ys5Tj634kqH9Y9NyTQ1Co.mg0zqkI.zCTLSFsGHLGKqqRqh8FRcsi', 'roles' => ['ROLE_USER'], 'plan' => 'pro', 'status' => 'active'],
    ],
    'usage' => [
        'demo-user' => ['plan' => 'starter', 'period' => '2026-06', 'requests' => 18420, 'bytes_in' => 2147483648, 'bytes_out' => 9126805504, 'connection_seconds' => 38540, 'countries' => ['DE' => 10200, 'FR' => 4300, 'NL' => 3920], 'errors' => 37, 'average_latency_ms' => 58],
        'maya-user' => ['plan' => 'pro', 'period' => '2026-06', 'requests' => 482100, 'bytes_in' => 8589934592, 'bytes_out' => 55834574848, 'connection_seconds' => 91820, 'countries' => ['US' => 211000, 'DE' => 120500, 'FR' => 80600, 'NL' => 70000], 'errors' => 91, 'average_latency_ms' => 64],
    ],
    'api_keys' => [
        'demo-user' => [
            ['id' => 'key-demo-primary', 'name' => 'Production traffic', 'prefix' => 'gp_9f3a21c8', 'created_at' => '2026-05-14T09:20:00+00:00', 'last_used_at' => '2026-06-29T08:48:00+00:00', 'auth_methods' => ['Authorization: Bearer', 'X-API-Key'], 'ip_whitelist' => ['198.51.100.24', '203.0.113.0/28']],
            ['id' => 'key-demo-ci', 'name' => 'CI smoke tests', 'prefix' => 'gp_2bc41e90', 'created_at' => '2026-06-01T15:10:00+00:00', 'last_used_at' => '2026-06-28T22:13:00+00:00', 'auth_methods' => ['X-API-Key'], 'ip_whitelist' => ['192.0.2.44']],
        ],
        'maya-user' => [
            ['id' => 'key-maya-primary', 'name' => 'Scraping workers', 'prefix' => 'gp_b719e2aa', 'created_at' => '2026-04-03T12:00:00+00:00', 'last_used_at' => '2026-06-29T10:05:00+00:00', 'auth_methods' => ['Authorization: Bearer'], 'ip_whitelist' => ['198.51.100.42']],
        ],
    ],
];
