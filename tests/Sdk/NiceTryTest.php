<?php

declare(strict_types=1);

namespace GeoProxy\Tests\Sdk;

use NiceTry\Sdk\NiceTry;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/sdk/php/NiceTry.php';

final class NiceTryTest extends TestCase
{
    public function testRequestPostsToFetchEndpointWithSimpleDomainAndCountry(): void
    {
        $client = new NiceTry('https://api.nicetry.test', 'gp_test', function (string $endpoint, array $options): array {
            self::assertSame('https://api.nicetry.test/v1/fetch', $endpoint);
            self::assertContains('Authorization: Bearer gp_test', $options['headers']);
            self::assertSame('https://google.com', $options['payload']['url']);
            self::assertSame('DE', $options['payload']['country']);
            self::assertSame('raw', $options['payload']['response']);

            return ['status' => 200, 'body' => '<html>ok</html>'];
        });

        self::assertSame('<html>ok</html>', $client->request('google.com', 'DE'));
    }

    public function testRequestEnvelopeReturnsDecodedJson(): void
    {
        $client = new NiceTry('https://api.nicetry.test', transport: fn(): array => [
            'status' => 200,
            'body' => '{"status":200,"country":"FR","body":"ok"}',
        ]);

        self::assertSame('FR', $client->requestEnvelope('https://example.com', 'FR')['country']);
    }
}
