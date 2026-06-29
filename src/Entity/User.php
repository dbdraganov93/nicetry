<?php

declare(strict_types=1);

namespace GeoProxy\Entity;

final readonly class User
{
    public function __construct(
        public string $id,
        public string $email,
        public string $planId,
        public bool $isActive,
    ) {}
}
