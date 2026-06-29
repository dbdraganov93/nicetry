<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BillingController
{
    #[Route('/v1/billing/plans', name: 'billing_plans', methods: ['GET'])]
    public function plans(Request $request): Response
    {
        return ApiResponse::json(['plans' => []]);
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
}
