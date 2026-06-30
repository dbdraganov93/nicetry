<?php

declare(strict_types=1);

namespace GeoProxy\Service\Payment;

use InvalidArgumentException;

final class PaymentGatewayRegistry
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways;

    /** @param iterable<PaymentGatewayInterface> $gateways */
    public function __construct(iterable $gateways = [])
    {
        $registered = [];
        foreach ($gateways as $gateway) {
            $registered[$gateway->provider()] = $gateway;
        }

        if ($registered === []) {
            $registered = [
                'stripe' => new StripePaymentGateway(),
                'paypal' => new PayPalPaymentGateway(),
            ];
        }

        $this->gateways = $registered;
    }

    /** @return array<string, list<string>> */
    public function supportedMethods(): array
    {
        $methods = [];
        foreach ($this->gateways as $provider => $gateway) {
            $methods[$provider] = $gateway->supportedMethods();
        }

        return $methods;
    }

    public function gateway(string $provider): PaymentGatewayInterface
    {
        return $this->gateways[$provider] ?? throw new InvalidArgumentException('unsupported_payment_provider');
    }
}
