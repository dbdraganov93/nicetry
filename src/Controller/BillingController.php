<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use GeoProxy\Service\BillingService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BillingController
{
    public function __construct(private readonly BillingService $billing = new BillingService()) {}

    #[Route('/v1/billing/plans', name: 'billing_plans', methods: ['GET'])]
    public function plans(Request $request): Response
    {
        return ApiResponse::json([
            'plans' => $this->billing->plans(),
            'payment_methods' => $this->billing->paymentMethods(),
            'recommended_card_gateway' => 'stripe',
            'wallets' => ['apple_pay', 'google_pay', 'link', 'paypal'],
        ]);
    }

    #[Route('/v1/billing/checkout', name: 'billing_checkout', methods: ['POST'])]
    public function checkout(Request $request): Response
    {
        $payload = $this->payload($request);

        try {
            $checkout = $this->billing->checkout(
                (string) ($payload['user_id'] ?? 'demo-user'),
                (string) ($payload['plan'] ?? 'starter'),
                (string) ($payload['provider'] ?? 'stripe'),
                (string) ($payload['method'] ?? 'card'),
                (string) ($payload['success_url'] ?? 'https://api.nicetry.example/dashboard?checkout=success'),
                (string) ($payload['cancel_url'] ?? 'https://api.nicetry.example/plans?checkout=cancelled'),
                (string) ($payload['currency'] ?? 'USD'),
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return ApiResponse::json(['checkout' => $checkout->toArray()], Response::HTTP_CREATED);
    }

    #[Route('/v1/billing/invoices', name: 'billing_invoices', methods: ['GET'])]
    public function invoices(Request $request): Response
    {
        return ApiResponse::json(['invoices' => []]);
    }

    #[Route('/webhooks/stripe', name: 'billing_stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request): Response
    {
        return ApiResponse::json(['received' => true]);
    }

    #[Route('/webhooks/paypal', name: 'billing_paypal_webhook', methods: ['POST'])]
    public function paypalWebhook(Request $request): Response
    {
        return ApiResponse::json(['received' => true]);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        if ($request->getContentTypeFormat() === 'json') {
            $decoded = json_decode($request->getContent() ?: '{}', true);

            return is_array($decoded) ? $decoded : [];
        }

        return $request->request->all();
    }
}
