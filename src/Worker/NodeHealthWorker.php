<?php

declare(strict_types=1);

namespace GeoProxy\Worker;

final class NodeHealthWorker
{
    /** @return array<string, bool|int> */
    public function check(string $proxyHealthUrl): array
    {
        $started = hrtime(true);
        $headers = @get_headers($proxyHealthUrl);

        return [
            'healthy' => is_array($headers) && str_contains($headers[0] ?? '', '200'),
            'latency_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
        ];
    }
}
