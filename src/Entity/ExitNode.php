<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

final readonly class ExitNode
{
    public function __construct(
        public string $id,
        public string $countryCode,
        public ?string $city,
        public string $vpnContainer,
        public string $proxyContainer,
        public bool $healthy,
        public int $activeConnections,
        public int $weight,
    ) {}
}
