<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Controller\AuthController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends TestCase
{
    public function testRegisterReturnsApiKeyRoutingPolicyAndFirstRequest(): void
    {
        $response = new AuthController()->register(Request::create('/auth/register', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'new-client@example.com',
            'password' => 'secret',
            'plan' => 'starter',
        ], JSON_THROW_ON_ERROR)));
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertSame('new-client', $payload['client_id']);
        self::assertStringStartsWith('gp_live_', $payload['api_key']['secret']);
        self::assertContains('DE', $payload['routing_policy']['allowed_countries']);
        self::assertStringContainsString('NiceTry', $payload['first_request']['php']);
    }
}
