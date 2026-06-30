<?php

declare(strict_types=1);

namespace GeoProxy\Tests;

use GeoProxy\Service\CommandRunner;
use GeoProxy\Service\NordVpnFetchService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NordVpnFetchServiceTest extends TestCase
{
    public function testFetchConnectsToCountryAndReturnsOriginBody(): void
    {
        $runner = new class extends CommandRunner {
            /** @var list<array<int, string>> */
            public array $commands = [];

            public function run(array $command, int $timeoutSeconds = 60): array
            {
                $this->commands[] = $command;

                if ($command[0] === 'nordvpn') {
                    return ['exit_code' => 0, 'output' => 'Connected to Germany'];
                }

                return ['exit_code' => 0, 'output' => '{"ok":true}' . "\n__GEOPROXY_HTTP_STATUS__:200\n__GEOPROXY_CONTENT_TYPE__:application/json"];
            }
        };

        $result = new NordVpnFetchService($runner)->fetch('https://example.com/data.json', 'Germany');

        self::assertSame('Germany', $runner->commands[0][2]);
        self::assertSame('https://example.com/data.json', end($runner->commands[1]));
        self::assertSame(200, $result['status']);
        self::assertSame('application/json', $result['content_type']);
        self::assertSame('{"ok":true}', $result['body']);
    }

    public function testRejectsLocalhostUrls(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('url_must_be_public_http_or_https');

        new NordVpnFetchService(new CommandRunner())->fetch('http://localhost/admin', 'DE');
    }
}
