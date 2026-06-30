<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use SensitiveParameter;

final class ClientOnboardingService
{
    /** @return array<string, mixed> */
    public function register(string $email, string $plan, #[SensitiveParameter] string $password): array
    {
        $clientId = $this->clientIdFromEmail($email);
        $apiKey = 'gp_live_' . bin2hex(random_bytes(16));

        return [
            'status' => 'registered',
            'email' => $email,
            'plan' => $plan,
            'client_id' => $clientId,
            'api_key' => [
                'prefix' => substr($apiKey, 0, 16),
                'secret' => $apiKey,
                'auth_methods' => ['Authorization: Bearer', 'X-API-Key'],
            ],
            'routing_policy' => [
                'allowed_countries' => $this->countriesForPlan($plan),
                'max_concurrent_requests' => $this->concurrencyForPlan($plan),
                'sticky_sessions_enabled' => in_array($plan, ['pro', 'enterprise'], true),
            ],
            'first_request' => [
                'php' => '$NiceTry = new NiceTry(\'https://api.nicetry.example\', getenv(\'NICETRY_API_KEY\')); $html = $NiceTry->request(\'google.com\', \'DE\');',
                'curl' => 'curl -H "Authorization: Bearer ' . $apiKey . '" -H "Content-Type: application/json" -d \'{"url":"https://google.com","country":"DE"}\' https://api.nicetry.example/v1/fetch',
            ],
        ];
    }

    /** @return list<string> */
    private function countriesForPlan(string $plan): array
    {
        return match ($plan) {
            'starter' => ['DE', 'FR', 'NL', 'US'],
            'pro', 'enterprise' => ['DE', 'FR', 'NL', 'US', 'IT', 'ES'],
            default => ['DE'],
        };
    }

    private function concurrencyForPlan(string $plan): int
    {
        return match ($plan) {
            'starter' => 50,
            'pro' => 500,
            'enterprise' => 2000,
            default => 5,
        };
    }

    private function clientIdFromEmail(string $email): string
    {
        $localPart = strtolower(strtok($email, '@') ?: 'client');
        $clientId = preg_replace('/[^a-z0-9]+/', '-', $localPart);

        return trim((string) $clientId, '-') ?: 'client';
    }
}
