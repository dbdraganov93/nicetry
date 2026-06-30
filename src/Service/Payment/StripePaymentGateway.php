<?php

declare(strict_types=1);

namespace GeoProxy\Service\Payment;

use InvalidArgumentException;

final class StripePaymentGateway implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'stripe';
    }

    public function supportedMethods(): array
    {
        return ['card', 'apple_pay', 'google_pay', 'link'];
    }

    public function createCheckout(string $userId, string $plan, int $amountCents, string $currency, string $successUrl, string $cancelUrl, string $method): PaymentCheckout
    {
        if (!in_array($method, $this->supportedMethods(), true)) {
            throw new InvalidArgumentException('unsupported_payment_method');
        }

        return new PaymentCheckout('stripe', $method, $plan, $amountCents, strtoupper($currency), 'https://checkout.stripe.com/c/pay_' . hash('xxh128', $userId . $plan . $method), [
            'mode' => 'subscription',
            'ui' => 'payment_element',
            'wallets' => ['apple_pay', 'google_pay', 'link'],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}
