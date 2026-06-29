<?php

declare(strict_types=1);

namespace GeoProxy\Service;

final class BillingService
{
    public function createCheckoutSessionPayload(string $userId, string $stripePriceId): array
    {
        return ['mode' => 'subscription', 'client_reference_id' => $userId, 'line_items' => [['price' => $stripePriceId, 'quantity' => 1]]];
    }
}
