<?php

declare(strict_types=1);

namespace GeoProxy\Service\Payment;

interface PaymentGatewayInterface
{
    public function provider(): string;

    /** @return list<string> */
    public function supportedMethods(): array;

    public function createCheckout(string $userId, string $plan, int $amountCents, string $currency, string $successUrl, string $cancelUrl, string $method): PaymentCheckout;
}
