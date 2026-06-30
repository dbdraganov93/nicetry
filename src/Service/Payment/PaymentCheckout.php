<?php

declare(strict_types=1);

namespace GeoProxy\Service\Payment;

final readonly class PaymentCheckout
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $provider,
        public string $method,
        public string $plan,
        public int $amountCents,
        public string $currency,
        public string $checkoutUrl,
        public array $metadata = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'method' => $this->method,
            'plan' => $this->plan,
            'amount_cents' => $this->amountCents,
            'currency' => $this->currency,
            'checkout_url' => $this->checkoutUrl,
            'metadata' => $this->metadata,
        ];
    }
}
