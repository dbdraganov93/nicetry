<?php

declare(strict_types=1);

namespace GeoProxy\Controller;

use GeoProxy\Service\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BillingController
{
    public function plans(Request $request): Response
    {
        return ApiResponse::json(['plans' => []]);
    }
    public function invoices(Request $request): Response
    {
        return ApiResponse::json(['invoices' => []]);
    }
    public function stripeWebhook(Request $request): Response
    {
        return ApiResponse::json(['received' => true]);
    }
}
