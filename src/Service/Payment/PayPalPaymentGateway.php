<?php

declare(strict_types=1);

namespace GeoProxy\Service\Payment;

use InvalidArgumentException;

final class PayPalPaymentGateway implements PaymentGatewayInterface
{
    public function provider(): string
    {
        return 'paypal';
    }

    public function supportedMethods(): array
    {
        return ['paypal', 'card', 'google_pay'];
    }

    public function createCheckout(string $userId, string $plan, int $amountCents, string $currency, string $successUrl, string $cancelUrl, string $method): PaymentCheckout
    {
        if (!in_array($method, $this->supportedMethods(), true)) {
            throw new InvalidArgumentException('unsupported_payment_method');
        }

        return new PaymentCheckout('paypal', $method, $plan, $amountCents, strtoupper($currency), 'https://www.paypal.com/checkoutnow?token=' . hash('xxh128', $userId . $plan . $method), [
            'intent' => 'CAPTURE',
            'components' => $method === 'google_pay' ? ['googlepay'] : ['buttons', 'card-fields'],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}
