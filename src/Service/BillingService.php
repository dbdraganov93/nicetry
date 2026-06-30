<?php

declare(strict_types=1);

namespace GeoProxy\Service;

use GeoProxy\Repository\FixtureRepository;
use GeoProxy\Service\Payment\PaymentCheckout;
use GeoProxy\Service\Payment\PaymentGatewayRegistry;
use InvalidArgumentException;

final class BillingService
{
    public function __construct(
        private readonly FixtureRepository $fixtures = new FixtureRepository(),
        private readonly PaymentGatewayRegistry $gateways = new PaymentGatewayRegistry(),
    ) {}

    /** @return array<string, mixed> */
    public function createCheckoutSessionPayload(string $userId, string $stripePriceId): array
    {
        return ['mode' => 'subscription', 'client_reference_id' => $userId, 'line_items' => [['price' => $stripePriceId, 'quantity' => 1]]];
    }

    /** @return list<array<string, mixed>> */
    public function plans(): array
    {
        return $this->fixtures->plans();
    }

    /** @return array<string, list<string>> */
    public function paymentMethods(): array
    {
        return $this->gateways->supportedMethods();
    }

    public function checkout(string $userId, string $planCode, string $provider, string $method, string $successUrl, string $cancelUrl, string $currency = 'USD'): PaymentCheckout
    {
        $plan = $this->planByCode($planCode);
        $amountCents = $plan['price_cents'];
        if ($amountCents === null) {
            throw new InvalidArgumentException('enterprise_plan_requires_sales_contact');
        }

        return $this->gateways->gateway($provider)->createCheckout($userId, $planCode, (int) $amountCents, $currency, $successUrl, $cancelUrl, $method);
    }

    /** @return array<string, mixed> */
    private function planByCode(string $planCode): array
    {
        foreach ($this->fixtures->plans() as $plan) {
            if ((string) $plan['code'] === $planCode) {
                return $plan;
            }
        }

        throw new InvalidArgumentException('unknown_plan');
    }
}
