<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Controller\BillingController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BillingControllerTest extends TestCase
{
    public function testPlansExposeStripePayPalAndWalletMethods(): void
    {
        $payload = $this->decode(new BillingController()->plans(Request::create('/v1/billing/plans')));

        self::assertSame('stripe', $payload['recommended_card_gateway']);
        self::assertContains('google_pay', $payload['payment_methods']['stripe']);
        self::assertContains('paypal', $payload['payment_methods']['paypal']);
        self::assertContains('google_pay', $payload['wallets']);
    }

    public function testCreatesStripeGooglePayCheckout(): void
    {
        $response = new BillingController()->checkout(Request::create('/v1/billing/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'user_id' => 'demo-user',
            'plan' => 'starter',
            'provider' => 'stripe',
            'method' => 'google_pay',
        ], JSON_THROW_ON_ERROR)));
        $payload = $this->decode($response);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame('stripe', $payload['checkout']['provider']);
        self::assertSame('google_pay', $payload['checkout']['method']);
        self::assertSame(2900, $payload['checkout']['amount_cents']);
    }

    public function testCreatesPayPalCheckout(): void
    {
        $response = new BillingController()->checkout(Request::create('/v1/billing/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'user_id' => 'demo-user',
            'plan' => 'starter',
            'provider' => 'paypal',
            'method' => 'paypal',
        ], JSON_THROW_ON_ERROR)));
        $payload = $this->decode($response);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame('paypal', $payload['checkout']['provider']);
        self::assertSame('paypal', $payload['checkout']['method']);
    }

    /** @return array<string, mixed> */
    private function decode(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
